import { json, badRequest, withAuth, sqliteConnect } from "./_lib";

export default function handler(req: Request): Response {
  return withAuth(req, () => {
    const file = new URL(req.url).searchParams.get("f");
    if (!file) return badRequest("Missing parameter: f");

    const db = sqliteConnect(file);
    const rows = db.query(`
      SELECT coins.id, images.image
      FROM coins LEFT OUTER JOIN images ON images.id = coins.image
    `).all() as [number, Buffer | null][];
    db.close();

    const data = rows.map(([id, image]) => [
      id,
      image ? Buffer.from(image).toString("base64") : null,
    ]);

    return json(data);
  }) as Response;
}
