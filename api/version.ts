import { json } from "./_lib";
import { version } from "../_version";

export default function handler(_req: Request): Response {
  return json(version);
}
