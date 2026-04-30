import { json, badRequest, withAuth, sqliteConnect } from "./_lib";

const INFO_FIELDS = [
  "coins.title", "obverseimg.image", "reverseimg.image",
  "status", "region", "country", "period", "ruler", "value", "unit", "type",
  "series", "subjectshort", "issuedate", "year", "mintage", "material",
  "mint", "mintmark", "features", "subject", "grade", "paydate", "payprice",
  "storage", "condition", "quantity",
];

export default function handler(req: Request): Response {
  return withAuth(req, () => {
    const p = new URL(req.url).searchParams;
    const file = p.get("f");
    const id   = p.get("id");
    if (!file || !id) return badRequest("Missing parameters: f, id");

    const db = sqliteConnect(file);
    const row = db.query(`
      SELECT ${INFO_FIELDS.join(",")} FROM coins
      LEFT JOIN photos AS obverseimg ON coins.obverseimg = obverseimg.id
      LEFT JOIN photos AS reverseimg ON coins.reverseimg = reverseimg.id
      WHERE coins.id = ?
    `).get(id) as unknown[] | null;
    db.close();

    if (!row) return json({ detail: "Not found" }, 404);

    const result = [...row] as unknown[];
    if (result[1]) result[1] = Buffer.from(result[1] as Buffer).toString("base64");
    if (result[2]) result[2] = Buffer.from(result[2] as Buffer).toString("base64");

    return json(result);
  }) as Response;
}
