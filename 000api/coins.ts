import { json, withAuth, getDb } from "./_lib";

export default function handler(req: Request): Response {
  return withAuth(req, () => {
    const p = new URL(req.url).searchParams;
    const db = getDb();

    let sql = `
      SELECT coins.id, title, status, subjectshort, value, unit, year, mintmark, series, country
      FROM coins
    `;

    const filters: string[] = [];
    const params: string[] = [];

    const add = (col: string, key: string) => {
      const v = p.get(key);
      if (v) { filters.push(`${col} = ?`); params.push(v); }
    };

    const search = p.get("search");
    if (search) { filters.push("title LIKE ?"); params.push(`%${search}%`); }

    add("status",  "status_filter");
    add("country", "country_filter");
    add("year",    "year_filter");
    add("series",  "series_filter");
    add("type",    "type_filter");
    add("period",  "period_filter");
    add("mint",    "mint_filter");

    if (filters.length) sql += ` WHERE ${filters.join(" AND ")}`;

    const sort = p.get("sort");
    sql += sort ? ` ORDER BY ${sort}` : " ORDER BY sort_id";
    if (p.get("reverse") === "true") sql += " DESC";

    const data = db.query(sql).all(...params);
    db.close();

    return json(data);
  }) as Response;
}
