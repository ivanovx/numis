import { NextRequest, NextResponse } from 'next/server';
import { openDb, getDbPath } from '@/lib/db';

export async function GET(request: NextRequest) {
  const { searchParams } = new URL(request.url);
  const file = searchParams.get('f');
  if (!file) return NextResponse.json({ error: 'No file specified' }, { status: 400 });

  try {
    const db = openDb(getDbPath(file));
    const result: Record<string, string[]> = {};

    for (const field of ['status', 'country', 'year', 'series', 'type', 'period', 'mint']) {
      const r = await db.execute(`SELECT DISTINCT IFNULL(${field},'') as val FROM coins ORDER BY ${field}`);
      result[field] = r.rows.map((row: any) => String(row[0]));
    }

    return NextResponse.json(result);
  } catch (err: any) {
    return NextResponse.json({ error: err.message }, { status: 500 });
  }
}
