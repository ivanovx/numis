import { json, badRequest, withAuth, sqliteConnect } from "./_lib";

const FIELD_IDS: Record<number, string> = {
  13: "status",  75: "region",    4: "country", 6: "period",  74: "ruler",
  10: "type",    11: "series",   12: "subjectshort", 9: "issuedate", 5: "year",
  25: "mintage", 14: "material",  7: "mint",     8: "mintmark", 20: "grade",
  40: "paydate", 41: "payprice", 67: "storage", 83: "condition", 71: "quantity",
   1: "title",
};

export default function handler(req: Request): Response {
  return withAuth(req, () => {
    const file = new URL(req.url).searchParams.get("f");
    if (!file) return badRequest("Missing parameter: f");

    const db = sqliteConnect(file);
    const settingsData = db.query("SELECT * FROM settings").all() as [string, string][];
    const fieldsData   = db.query(
      `SELECT id, title FROM fields WHERE id IN (${Object.keys(FIELD_IDS).join(",")})`
    ).all() as [number, string][];
    db.close();

    const result: Record<string, unknown> = { statuses: {}, fields: {} };

    for (const [title, val] of settingsData) {
      if (title === "Version") {
        result.version = parseInt(val, 10);
      } else if (title === "Password") {
        result.password = val;
      } else if (title === "Type") {
        result.type = val;
      } else if (title === "convert_fraction" || title === "enable_bc") {
        result[title] = ["true", "1"].includes(val.toLowerCase());
      } else if (title.endsWith("_status_title")) {
        const key = title.slice(0, -"_status_title".length);
        (result.statuses as Record<string, string>)[key] = val;
      }
    }

    for (const [fieldId, fieldTitle] of fieldsData) {
      const name = FIELD_IDS[fieldId];
      if (name) (result.fields as Record<string, string>)[name] = fieldTitle;
    }

    return json(result);
  }) as Response;
}
