import { openDb, getDbPath } from '../lib/db';

export function handleCoin(url: URL): Response {
  const file = url.searchParams.get('f');
  const id = url.searchParams.get('id');

  if (!file || !id) return Response.json({ error: 'Missing parameters' }, { status: 400 });

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

    const coin = db.query(sql).get(parseInt(id)) as any;
    db.close();

    if (!coin) return Response.json({ error: 'Coin not found' }, { status: 404 });

    if (coin.obverseImg instanceof Uint8Array) coin.obverseImg = Buffer.from(coin.obverseImg).toString('base64');
    if (coin.reverseImg instanceof Uint8Array) coin.reverseImg = Buffer.from(coin.reverseImg).toString('base64');

    return Response.json(coin);
  } catch (err: any) {
    return Response.json({ error: err.message }, { status: 500 });
  }
}
