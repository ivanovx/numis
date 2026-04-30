import { json, withAuth, getDb } from "./_lib";

export default function handler(req: Request): Response {
  return withAuth(req, () => {
    const db = getDb();
    const scalar = (sql: string) =>
      (db.query(sql).get() as Record<string, unknown>).v;

    const OWNED = `'owned','ordered','sale','duplicate','replacement'`;

    const summary: Record<string, unknown> = {
      total_count:             scalar("SELECT count(*) AS v FROM coins"),
      count_owned:             scalar(`SELECT count(*) AS v FROM coins WHERE status IN (${OWNED})`),
      count_wish:              scalar("SELECT count(*) AS v FROM coins WHERE status='wish'"),
      count_sold:              scalar("SELECT count(*) AS v FROM coins WHERE status='sold'"),
      count_bidding:           scalar("SELECT count(*) AS v FROM coins WHERE status='bidding'"),
      count_missing:           scalar("SELECT count(*) AS v FROM coins WHERE status='missing'"),
      paid:                    scalar(`SELECT SUM(totalpayprice) AS v FROM coins WHERE status IN (${OWNED},'sold','missing') AND totalpayprice<>'' AND totalpayprice IS NOT NULL`),
      paid_without_commission: scalar(`SELECT SUM(payprice)      AS v FROM coins WHERE status IN (${OWNED},'sold','missing') AND payprice<>''      AND payprice      IS NOT NULL`),
    };

    const firstRow = db.query(`
      SELECT paydate AS v FROM coins
      WHERE status IN (${OWNED},'sold','missing')
        AND paydate<>'' AND paydate IS NOT NULL
      ORDER BY paydate LIMIT 1
    `).get() as { v: string } | null;

    if (firstRow?.v) summary.first_purchase = firstRow.v;

    db.close();
    return json(summary);
  }) as Response;
}
