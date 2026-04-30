import { json, badRequest, withAuth, sqliteConnect } from "./_lib";

export default function handler(req: Request): Response {
  return withAuth(req, () => {
    const p = new URL(req.url).searchParams;
    const file = p.get("f");
    const id   = p.get("id");
    if (!file || !id) return badRequest("Missing parameters: f, id");

    const db = sqliteConnect(file);
    const row = db.query(`
      SELECT obverseimg.image, reverseimg.image, edgeimg.image,
             photo1.image, photo2.image, photo3.image, photo4.image
      FROM coins
      LEFT JOIN photos AS obverseimg ON coins.obverseimg = obverseimg.id
      LEFT JOIN photos AS reverseimg ON coins.reverseimg = reverseimg.id
      LEFT JOIN photos AS edgeimg    ON coins.edgeimg    = edgeimg.id
      LEFT JOIN photos AS photo1     ON coins.photo1     = photo1.id
      LEFT JOIN photos AS photo2     ON coins.photo2     = photo2.id
      LEFT JOIN photos AS photo3     ON coins.photo3     = photo3.id
      LEFT JOIN photos AS photo4     ON coins.photo4     = photo4.id
      WHERE coins.id = ?
    `).get(id) as Record<string, Buffer | null> | null;
    db.close();

    const result: string[] = [];
    if (row) {
      for (const val of Object.values(row)) {
        if (val) result.push(Buffer.from(val).toString("base64"));
      }
    }

    return json(result);
  }) as Response;
}
