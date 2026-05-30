import { renderToString } from "react-dom/server";
import { AppShell } from "./components/App";
import { handleFilelist } from "./routes/filelist";
import { handleFilters } from "./routes/filters";
import { handleCoins } from "./routes/coins";
import { handleCoin } from "./routes/coin";

// Build client bundle once at startup
const clientBundle = await Bun.build({
  entrypoints: ["./src/client.tsx"],
  target: "browser",
  minify: false,
  external: [],
});

const clientJs = clientBundle.outputs[0]
  ? await clientBundle.outputs[0].text()
  : "";

const PORT = parseInt(process.env.PORT || "3000");

console.log(`Numus website running on http://localhost:${PORT}`);

Bun.serve({
  port: PORT,
  async fetch(req) {
    const url = new URL(req.url);

    // API routes
    if (url.pathname === "/api/filelist") return handleFilelist();
    if (url.pathname === "/api/filters") return handleFilters(url);
    if (url.pathname === "/api/coins") return handleCoins(url);
    if (url.pathname === "/api/coin") return handleCoin(url);

    // Serve bundled client JS
    if (url.pathname === "/client.js") {
      return new Response(clientJs, {
        headers: { "Content-Type": "application/javascript" },
      });
    }

    const html = "<!DOCTYPE html>" + renderToString(<AppShell />);
    return new Response(html, {
      headers: { "Content-Type": "text/html; charset=utf-8" },
    });
  },
});
