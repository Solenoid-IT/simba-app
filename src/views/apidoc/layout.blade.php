<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        :root
        {
            --bg-main: #1a191f;
            --bg-card: #25232d;
            --bg-header: #2d2b36;
            --accent: #078e6e;
            --accent-dto: #e3a008;
            --accent-list: #3f83f8;
            --text-muted: #888;

            --bg-main: #0d1117;
            --bg-card: #161b22;
            --bg-header: #21262d;
            --border-color: #30363d;
            
            /* Colori stile Sintassi JSON */
            --json-key: #79c0ff;
            --json-str: #a5d6ff;
            --json-num: #d2a8ff;
            --json-bool: #ff7b72;
            --json-bracket: #d1d5da;
            
            --accent-folder: #238636;
            --text-muted: #8b949e;
        }

        body
        {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-main);
            color: #e0e0e0;
            padding-bottom: 50px;
        }

        /* Struttura Ricorsiva ad Albero */
        .tree-level
        {
            margin-top: 0.5rem;
            border-left: 2px solid #34323c;
            padding-left: 1.5rem;
            margin-left: 0.5rem;
        }

        .folder-node, .class-node
        {
            margin-bottom: 1rem;
        }

        /* Titoli Interattivi */
        .folder-title, .class-title
        {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
            padding: 5px 0;
            transition: opacity 0.2s;
        }

        .folder-title { color: var(--accent); }
        .class-title { color: #fff; text-transform: none; letter-spacing: normal; }

        .folder-title:hover, .class-title:hover { opacity: 0.8; }

        .folder-title::before { content: "📂"; margin-right: 10px; }
        .folder-title.collapsed::before { content: "📁"; }
        .class-title::before { /*content: "📄";*/content: "📦"; margin-right: 10px; filter: grayscale(1); }

        /* API Card Style */
        .api-card
        {
            background-color: var(--bg-card);
            border: 1px solid #34323c;
            border-radius: 10px;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .api-header
        {
            padding: 0.8rem 1.2rem;
            background-color: var(--bg-header);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .method-name
        {
            font-family: 'Fira Code', monospace;
            font-size: 1.05rem;
            color: #4ec9b0;
        }

        /*

        .method-read
        {
            background: #00ff0033;
            border-radius: 4px;
            padding: 2px 10px;
            color: #00ff00;
        }

        .method-update,
        .method-upsert
        {
            background: #ffff0033;
            border-radius: 4px;
            padding: 2px 10px;
            color: #ffff00;
        }

        .method-insert
        {
            background: #00ffff33;
            border-radius: 4px;
            padding: 2px 10px;
            color: #00ffff;
        }

        .method-delete
        {
            background: #ff000033;
            border-radius: 4px;
            padding: 2px 10px;
            color: #ff0000;
        }

        */



        .method-read
        {
            background: rgba(52, 211, 153, 0.15); /* Verde Smeraldo soft */
            border: 1px solid rgba(52, 211, 153, 0.3);
            border-radius: 6px;
            padding: 2px 10px;
            color: #10b981;
        }

        .method-update,
        .method-upsert,
        .method-set
        {
            background: rgba(251, 191, 36, 0.15); /* Ambra/Arancio soft */
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 6px;
            padding: 2px 10px;
            color: #d97706;
        }

        .method-insert
        {
            background: rgba(59, 130, 246, 0.15); /* Blu moderno (invece del Ciano) */
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 6px;
            padding: 2px 10px;
            color: #2563eb;
        }

        .method-delete
        {
            background: rgba(239, 68, 68, 0.15); /* Rosso corallo soft */
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 6px;
            padding: 2px 10px;
            color: #dc2626;
        }
        
        .method-neuter
        {
            background: rgba(107, 114, 128, 0.15); /* Grigio Ardesia soft */
            border: 1px solid rgba(107, 114, 128, 0.3);
            border-radius: 6px;
            padding: 2px 10px;
            color: var(--bs-secondary-color);
        }




        .dto-read
        {
            border-left: 3px solid rgba(52, 211, 153, 0.3);
        }
        
        .dto-update,
        .dto-upsert,
        .dto-set
        {
            border-left: 3px solid rgba(251, 191, 36, 0.3);
        }
        
        .dto-insert
        {
            border-left: 3px solid rgba(59, 130, 246, 0.3);
        }
        
        .dto-delete
        {
            border-left: 3px solid rgba(239, 68, 68, 0.3);
        }



        /* Tags */
        .io-type-tag
        {
            font-family: 'Inter', sans-serif;
            font-size: 0.7rem;
            margin: 14px 20px;
            padding: 3px 10px;
            display: inline-block;
            border-radius: 20px;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .tag-value { background: rgba(7, 142, 110, 0.15); color: #10b981; }
        .tag-dto { background: rgba(227, 160, 8, 0.15); color: #e3a008; }
        .tag-list { background: rgba(63, 131, 248, 0.15); color: #3f83f8; }

        /* Buttons & Tables */
        .btn-collapse
        {
            font-size: 0.75rem;
            padding: 4px 12px;
            color: #aaa;
            border: 1px solid #444;
            background: transparent;
            border-radius: 6px;
        }

        .btn-collapse .chevron
        {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(45deg);
            margin-left: 8px;
            transition: transform 0.3s ease;
            margin-bottom: 3px;
        }

        .btn-collapse[aria-expanded="true"] .chevron
        {
            transform: rotate(-135deg);
            margin-bottom: -1px;
        }

        .table-reflection { font-size: 0.85rem; margin-bottom: 0; }
        .table-reflection th { font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; }

        .dto-structure { /*border-left: 3px solid var(--accent-dto); background: rgba(227, 160, 8, 0.03);*/ padding: 1rem; margin: 10px; }
        .list-structure { /*border-left: 3px solid var(--accent-list); background: rgba(63, 131, 248, 0.03);*/ /*padding: 1rem;*/ margin: 10px; }

        .val-bool.true { color: #4ec9b0; }
        .val-bool.false { color: #f44747; }
        .val-num { color: #b5cea8; }
        .val-str { color: #ce9178; }
        code { background: #1e1e1e; color: #d4d4d4; font-weight: normal; }

        .json-explorer
        {
            padding: 0 12px;
            font-family: 'Fira Code', monospace;
            font-size: 0.9rem;
        }

        .json-bracket
        {
            color: var(--json-bracket);
            font-weight: bold;
        }
        
        .json-row
        {
            display: flex;
            flex-direction: column;
            margin-bottom: 0.5rem;
            padding-left: 1.5rem;
            border-left: 1px solid #30363d;
        }

        .json-key-line
        {
            display: flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }

        .json-key
        {
            color: var(--json-key);
            margin-right: 8px;
        }

        .json-colon
        {
            color: #fff; margin-right: 8px;
        }

        .type-badge
        {
            font-size: 0.7rem;
            padding: 1px 6px;
            border-radius: 4px;
            background: #21262d;
            border: 1px solid #30363d;
            color: var(--text-muted);
            margin-left: 10px;
        }

        /* Tabelle Reflection interne */
        .rules-container
        {
            margin: 15px 20px;
            margin-top: 0;
            background: #0d1117;
            border: 1px solid #30363d;
            border-radius: 6px;
            overflow: hidden;
        }

        .table-reflection
        {
            width: 100%;
            margin-bottom: 0;
            font-size: 0.8rem;
            color: #8b949e;
        }

        .table-reflection th
        {
            background: #161b22;
            padding: 6px 12px;
            text-transform: uppercase;
            font-size: 0.65rem;
            border-bottom: 1px solid #30363d;
        }

        .table-reflection td 
        {
            padding: 6px 12px;
            border-bottom: 1px solid #21262d;
        }

        /* Valori JSON */
        .val-bool { color: var(--json-bool); }
        .val-num { color: var(--json-num); }
        .val-str { color: var(--json-str); }

        .collapse-indicator
        {
            font-size: 0.6rem;
            margin-right: 8px;
            transition: transform 0.2s;
            color: var(--text-muted);
        }

        .collapsed .collapse-indicator { transform: rotate(-90deg); }

        .table-box
        {
            padding: 10px 20px;
            font-size: 12px;
            font-weight: 600;
            background-color: #1a2028;
            border-bottom: 1px solid #202732;
        }

    </style>
</head>
<body>
    <main>
        @yield ( 'content' )
    </main>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>