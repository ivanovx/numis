import { NextRequest, NextResponse } from 'next/server';
import { openDb, getDbPath } from '@/lib/db';

export async function GET(request: NextRequest) {
  const { searchParams } = new URL(request.url);
  const file = searchParams.get('f');
  const search = searchParams.get('search');
  const statusFilter = searchParams.get('status_filter');
  const countryFilter = searchParams.get('country_filter');
  const yearFilter = searchParams.get('year_filter');

  if (!file) return NextResponse.json({ error: 'No file specified' }, { status: 400 });

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

    if (search)        { filters.push("title LIKE ?");   args.push(`%${search}%`); }
    if (statusFilter)  { filters.push("status = ?");     args.push(statusFilter); }
    if (countryFilter) { filters.push("country = ?");    args.push(countryFilter); }
    if (yearFilter)    { filters.push("year = ?");       args.push(yearFilter); }

    if (filters.length) sql += ` WHERE ${filters.join(' AND ')}`;
    sql += ' ORDER BY year ASC, value ASC';

    const result = await db.execute({ sql, args });

    const coins = result.rows.map((row: any) => {
      const obj: Record<string, any> = {};
      result.columns.forEach((col, i) => { obj[col] = row[i]; });
      if (obj.image && obj.image instanceof Uint8Array) {
        obj.image = Buffer.from(obj.image).toString('base64');
      } else if (typeof obj.image === 'string') {
        // already base64 in some libsql versions
      }
      return obj;
    });

    return NextResponse.json(coins);
  } catch (err: any) {
    return NextResponse.json({ error: err.message }, { status: 500 });
  }
}
