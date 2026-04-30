import sharp from "sharp";
import { json, badRequest, withAuth, sqliteConnect, MAX_PREVIEW_IMAGE_HEIGHT } from "./_lib";

export default async function handler(req: Request): Promise<Response> {
  return withAuth(req, async () => {
    const p = new URL(req.url).searchParams;
    const file = p.get("f");
    const id   = p.get("id");
    const type = p.get("type");
    if (!file || !id || !type) return badRequest("Missing parameters: f, id, type");

    const db = sqliteConnect(file);
    let imgData: string = "";

    if (type === "obverse" || type === "reverse") {
      const table = type === "obverse" ? "obverseimg" : "reverseimg";
      const col   = type === "obverse" ? "obverseimg" : "reverseimg";
      const row = db.query(`
        SELECT ${table}.image FROM coins
        LEFT JOIN photos AS ${table} ON coins.${col} = ${table}.id
        WHERE coins.id = ?
      `).get(id) as { image: Buffer } | null;
      db.close();

      if (row?.image) imgData = Buffer.from(row.image).toString("base64");

    } else {
      // type === "both" — resize and composite side by side
      const row = db.query(`
        SELECT obverseimg.image AS img1, reverseimg.image AS img2 FROM coins
        LEFT JOIN photos AS obverseimg ON coins.obverseimg = obverseimg.id
        LEFT JOIN photos AS reverseimg ON coins.reverseimg = reverseimg.id
        WHERE coins.id = ?
      `).get(id) as { img1: Buffer | null; img2: Buffer | null } | null;
      db.close();

      const resize = (buf: Buffer | null) =>
        buf
          ? sharp(buf)
              .resize({ height: MAX_PREVIEW_IMAGE_HEIGHT, withoutEnlargement: true })
              .toBuffer({ resolveWithObject: true })
          : null;

      const [r1, r2] = await Promise.all([resize(row?.img1 ?? null), resize(row?.img2 ?? null)]);

      if (r1 || r2) {
        const w1 = r1?.info.width ?? 0;
        const w2 = r2?.info.width ?? 0;
        const composites: sharp.OverlayOptions[] = [];
        if (r1) composites.push({ input: r1.data, left: 0,  top: 0 });
        if (r2) composites.push({ input: r2.data, left: w1, top: 0 });

        const combined = await sharp({
          create: {
            width: w1 + w2,
            height: MAX_PREVIEW_IMAGE_HEIGHT,
            channels: 4,
            background: { r: 0, g: 0, b: 0, alpha: 0 },
          },
        })
          .composite(composites)
          .webp({ lossless: false, quality: 80 })
          .toBuffer();

        imgData = combined.toString("base64");
      }
    }

    return json(imgData);
  }) as Promise<Response>;
}
