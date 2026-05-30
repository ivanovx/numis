import { openDb, getDbPath } from '../lib/db';

export function handleCoins(url: URL): Response {
  const file = url.searchParams.get('f');
  const search = url.searchParams.get('search');
  const statusFilter = url.searchParams.get('status_filter');
  const countryFilter = url.searchParams.get('country_filter');
  const yearFilter = url.searchParams.get('year_filter');

  if (!file) return Response.json({ error: 'No file specified' }, { status: 400 });

  try {
    const db = openDb(getDbPath(file));

    let sql = `
      SELECT coins.id, title, status, subjectshort, value, unit, year, mintmark, series, country,
             images.image
      FROM coins
      LEFT OUTER JOIN images ON images.id = coins.image
    `;

    const args: any[] = [];
    const filters: string[] = [];

    if (search)        { filters.push('title LIKE ?');  args.push(`%${search}%`); }
    if (statusFilter)  { filters.push('status = ?');    args.push(statusFilter); }
    if (countryFilter) { filters.push('country = ?');   args.push(countryFilter); }
    if (yearFilter)    { filters.push('year = ?');      args.push(yearFilter); }

    if (filters.length) sql += ` WHERE ${filters.join(' AND ')}`;
    sql += ' ORDER BY year ASC, value ASC';

    const rows = db.query(sql).all(...args) as any[];

    const coins = rows.map(row => {
      if (row.image instanceof Uint8Array) {
        row.image = Buffer.from(row.image).toString('base64');
      }
      return row;
    });

    db.close();
    return Response.json(coins);
  } catch (err: any) {
    return Response.json({ error: err.message }, { status: 500 });
  }
}
