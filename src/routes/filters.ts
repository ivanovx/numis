import { openDb, getDbPath } from '../lib/db';

export function handleFilters(url: URL): Response {
  const file = url.searchParams.get('f');
  if (!file) return Response.json({ error: 'No file specified' }, { status: 400 });

  try {
    const db = openDb(getDbPath(file));
    const result: Record<string, string[]> = {};

    for (const field of ['status', 'country', 'year', 'series', 'type', 'period', 'mint']) {
      const rows = db.query(`SELECT DISTINCT IFNULL(${field},'') as val FROM coins ORDER BY ${field}`).all() as any[];
      result[field] = rows.map(r => String(r.val));
    }

    db.close();
    return Response.json(result);
  } catch (err: any) {
    return Response.json({ error: err.message }, { status: 500 });
  }
}
