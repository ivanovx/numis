// Client-side React app - rendered as a static HTML shell
// The actual interactivity is handled by client.tsx bundled separately

export function AppShell() {
  return (
    <html lang="en">
      <head>
        <meta charSet="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Coin Catalog</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>{`
          * { box-sizing: border-box; }
          body { margin: 0; background: #0a0a14; color: white; font-family: system-ui, sans-serif; }
          .animate-pulse { animation: pulse 2s cubic-bezier(0.4,0,0.6,1) infinite; }
          .animate-spin { animation: spin 1s linear infinite; }
          @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
          @keyframes spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
          .line-clamp-2 { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        `}</style>
      </head>
      <body>
        <div id="root"></div>
        <script src="/client.js"></script>
      </body>
    </html>
  );
}
