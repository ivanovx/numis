import { openDb, getDbPath, listDbFiles, blobToBase64 } from "../db";

function json(data: unknown, status = 200): Response {
  return new Response(JSON.stringify(data), {
    status,
    headers: { "Content-Type": "application/json" },
  });
}

export function handleFilelist(): Response {
  try {
    return json(listDbFiles());
  } catch (err: any) {
    return json({ error: err.message }, 500);
  }
}

export function handleCoins(url: URL): Response {
  const file = url.searchParams.get("f");
  const search = url.searchParams.get("search");
  const statusFilter = url.searchParams.get("status_filter");
  const countryFilter = url.searchParams.get("country_filter");
  const yearFilter = url.searchParams.get("year_filter");

  if (!file) return json({ error: "No file specified" }, 400);

  try {
    const db = openDb(getDbPath(file));

    const filters: string[] = [];
    const args: unknown[] = [];

    if (search) {
      filters.push("title LIKE ?");
      args.push(`%${search}%`);
    }
    if (statusFilter) {
      filters.push("status = ?");
      args.push(statusFilter);
    }
    if (countryFilter) {
      filters.push("country = ?");
      args.push(countryFilter);
    }
    if (yearFilter) {
      filters.push("year = ?");
      args.push(yearFilter);
    }

    const where = filters.length ? `WHERE ${filters.join(" AND ")}` : "";

    const sql = `
      SELECT coins.id, title, status, subjectshort, value, unit, year, mintmark, series, country,
             images.image
      FROM coins
      LEFT OUTER JOIN images ON images.id = coins.image
      ${where}
      ORDER BY year ASC, value ASC
    `;

    const rows = db.query(sql).all(...args) as Record<string, unknown>[];

    const coins = rows.map((row) => ({
      ...row,
      image: blobToBase64(row.image),
    }));

    db.close();
    return json(coins);
  } catch (err: any) {
    return json({ error: err.message }, 500);
  }
}

export function handleCoin(url: URL): Response {
  const file = url.searchParams.get("f");
  const id = url.searchParams.get("id");

  if (!file || !id) return json({ error: "Missing parameters" }, 400);

  try {
    const db = openDb(getDbPath(file));

    const sql = `
      SELECT coins.id, coins.title,
             obverseimg.image AS obverseImg, reverseimg.image AS reverseImg,
             status, region, country, period, ruler, value, unit, type,
             series, subjectshort, issuedate, year, mintage, material,
             mint, mintmark, features, subject, grade, paydate, payprice,
             storage, condition, quantity
      FROM coins
      LEFT JOIN photos AS obverseimg ON coins.obverseimg = obverseimg.id
      LEFT JOIN photos AS reverseimg ON coins.reverseimg = reverseimg.id
      WHERE coins.id = ?
    `;

    const row = db.query(sql).get(parseInt(id)) as Record<
      string,
      unknown
    > | null;
    db.close();

    if (!row) return json({ error: "Coin not found" }, 404);

    return json({
      ...row,
      obverseImg: blobToBase64(row.obverseImg),
      reverseImg: blobToBase64(row.reverseImg),
    });
  } catch (err: any) {
    return json({ error: err.message }, 500);
  }
}

export function handleFilters(url: URL): Response {
  const file = url.searchParams.get("f");
  if (!file) return json({ error: "No file specified" }, 400);

  try {
    const db = openDb(getDbPath(file));
    const result: Record<string, string[]> = {};

    for (const field of [
      "status",
      "country",
      "year",
      "series",
      "type",
      "period",
      "mint",
    ]) {
      const rows = db
        .query(
          `SELECT DISTINCT IFNULL(${field},'') as val FROM coins ORDER BY ${field}`,
        )
        .all() as { val: string }[];
      result[field] = rows.map((r) => String(r.val));
    }

    db.close();
    return json(result);
  } catch (err: any) {
    return json({ error: err.message }, 500);
  }
}
