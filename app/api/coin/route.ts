import { NextRequest, NextResponse } from 'next/server';
import { openDb, getDbPath } from '@/lib/db';

export async function GET(request: NextRequest) {
  const { searchParams } = new URL(request.url);
  const file = searchParams.get('f');
  const id = searchParams.get('id');

  if (!file || !id) return NextResponse.json({ error: 'Missing parameters' }, { status: 400 });

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

    const result = await db.execute({ sql, args: [parseInt(id)] });

    if (!result.rows.length) return NextResponse.json({ error: 'Coin not found' }, { status: 404 });

    const coin: Record<string, any> = {};
    result.columns.forEach((col, i) => { coin[col] = result.rows[0][i]; });

    if (coin.obverseImg instanceof Uint8Array) coin.obverseImg = Buffer.from(coin.obverseImg).toString('base64');
    if (coin.reverseImg instanceof Uint8Array) coin.reverseImg = Buffer.from(coin.reverseImg).toString('base64');

    return NextResponse.json(coin);
  } catch (err: any) {
    return NextResponse.json({ error: err.message }, { status: 500 });
  }
}
