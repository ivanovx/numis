import { json, badRequest, withAuth, sqliteConnect } from "./_lib";

const FILTER_FIELDS = ["status", "country", "year", "series", "type", "period", "mint"] as const;

export default function handler(req: Request): Response {
  return withAuth(req, () => {
    const file = new URL(req.url).searchParams.get("f");
    if (!file) return badRequest("Missing parameter: f");

    const db = sqliteConnect(file);
    const result: Record<string, string[]> = {};

    for (const field of FILTER_FIELDS) {
      const rows = db.query(
        `SELECT DISTINCT IFNULL(${field},'') FROM coins ORDER BY ${field}`
      ).all() as [string][];
      result[field] = rows.map(([v]) => v);
    }

    db.close();
    return json(result);
  }) as Response;
}
