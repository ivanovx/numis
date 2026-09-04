<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GraphiQL</title>
    <link rel="stylesheet" href="https://unpkg.com/graphiql@3.8.3/graphiql.min.css">
    <style>
        html, body, #graphiql { height: 100%; margin: 0; }
        .graphql-toolbar {
            position: fixed;
            top: 8px;
            right: 16px;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 8px;
            border: 1px solid #d6d6d6;
            border-radius: 4px;
            background: #fff;
            font: 13px sans-serif;
        }
        .graphql-toolbar select { padding: 3px; }
    </style>
</head>
<body>
    <div class="graphql-toolbar">
        <span>Language</span>
        <select id="graphql-locale" aria-label="GraphQL response language">
            <option value="bg">Български</option>
            <option value="en" selected>English</option>
            <option value="de">Deutsch</option>
        </select>
    </div>
    <div id="graphiql"></div>

    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/graphiql@3.8.3/graphiql.min.js"></script>
    <script>
        const locale = document.querySelector('#graphql-locale');
        const fetcher = (graphQLParams) => fetch('{{ url('/graphql') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ ...graphQLParams, locale: locale.value }),
        }).then(response => response.json());

        const query = `query Coins {
  coins {
    id
    title
    year
    metal
    seriesName
  }
}`;

        ReactDOM.createRoot(document.querySelector('#graphiql')).render(
            React.createElement(GraphiQL, { fetcher, defaultQuery: query })
        );
    </script>
</body>
</html>
    <style>
        :root { color-scheme: dark; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
        body { margin: 0; background: #101820; color: #e9f0f2; }
        main { display: grid; gap: 1rem; max-width: 1500px; margin: auto; padding: 1.5rem; }
        header, .toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
        h1 { margin: 0; font: 700 1.5rem Georgia, serif; }
        .toolbar { justify-content: flex-start; }
        button, select { border: 1px solid #55717b; border-radius: 4px; padding: .65rem .8rem; background: #1d3039; color: inherit; font: inherit; cursor: pointer; }
        button[type="submit"] { background: #c36d3d; border-color: #c36d3d; color: #fff; font-weight: 700; }
        label { display: grid; gap: .35rem; color: #a9c0c6; font-size: .8rem; }
        .editors { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        textarea, pre { box-sizing: border-box; width: 100%; min-height: 52vh; margin: 0; padding: 1rem; border: 1px solid #304a55; border-radius: 4px; background: #0b1217; color: #d9f1ed; font: .9rem/1.5 ui-monospace, SFMono-Regular, Menlo, monospace; resize: vertical; }
        pre { overflow: auto; white-space: pre-wrap; }
        @media (max-width: 800px) { .editors { grid-template-columns: 1fr; } textarea, pre { min-height: 32vh; } }
    </style>
</head>
<body>
<main>
    <header><h1>GraphQL UI</h1><span>POST /graphql</span></header>
    <form id="graphql-form">
        <div class="toolbar">
            <label>Language
                <select id="locale"><option value="bg">Български</option><option value="en" selected>English</option><option value="de">Deutsch</option></select>
            </label>
            <button type="submit">Run query</button>
            <button type="button" id="clear">Clear</button>
        </div>
        <div class="editors">
            <textarea id="query" spellcheck="false">query Coins {
  coins {
    id
    title
    year
    metal
    seriesName
  }
}</textarea>
            <pre id="result">Run a query to see the response.</pre>
        </div>
    </form>
</main>
<script>
const form = document.querySelector('#graphql-form');
const query = document.querySelector('#query');
const result = document.querySelector('#result');
const locale = document.querySelector('#locale');

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    result.textContent = 'Loading...';
    try {
        const response = await fetch('{{ url('/graphql') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ query: query.value, locale: locale.value }),
        });
        const body = await response.json();
        result.textContent = JSON.stringify(body, null, 2);
    } catch (error) {
        result.textContent = error.message;
    }
});

document.querySelector('#clear').addEventListener('click', () => {
    query.value = '';
    result.textContent = '';
});
</script>
</body>
</html>