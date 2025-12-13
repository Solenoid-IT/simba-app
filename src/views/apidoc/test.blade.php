<!DOCTYPE html>
<html lang="it" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Reference Template - Advanced Inputs</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-main: #1a191f;
            --bg-card: #25232d;
            --bg-header: #2d2b36;
            --accent: #078e6e;
            --accent-dto: #e3a008;
            --accent-list: #3f83f8;
            --text-muted: #888;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-main);
            color: #e0e0e0;
            padding-bottom: 50px;
        }

        /* Struttura Ricorsiva ad Albero */
        .tree-level {
            margin-top: 0.5rem;
            border-left: 2px solid #34323c;
            padding-left: 1.5rem;
            margin-left: 0.5rem;
        }

        .folder-node {
            margin-bottom: 1rem;
        }

        /* Titolo Cartella e Classe Interattivo */
        .folder-title, .class-title {
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
        .class-title { 
            color: #fff; 
            text-transform: none; 
            letter-spacing: normal;
            opacity: 0.9;
        }

        .folder-title:hover, .class-title:hover {
            opacity: 0.8;
        }

        .folder-title::before {
            content: "📂";
            margin-right: 10px;
            transition: transform 0.2s;
        }

        .folder-title.collapsed::before {
            content: "📁";
        }

        .class-title::before {
            content: "📄";
            margin-right: 10px;
            filter: grayscale(1);
        }

        .class-title.collapsed::before {
            opacity: 0.5;
        }

        .class-node {
            margin-bottom: 1.5rem;
            margin-top: 0.5rem;
        }

        /* Card della Funzione */
        .api-card {
            background-color: var(--bg-card);
            border: 1px solid #34323c;
            border-radius: 10px;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .api-header {
            padding: 0.8rem 1.2rem;
            background-color: var(--bg-header);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .method-name {
            font-family: 'Fira Code', monospace;
            font-size: 1.05rem;
            color: #4ec9b0;
            font-weight: 500;
        }

        /* Badge stili per Input Type */
        .io-type-tag {
            font-size: 0.7rem;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .tag-value { background: rgba(7, 142, 110, 0.15); color: #10b981; }
        .tag-dto { background: rgba(227, 160, 8, 0.15); color: #e3a008; }
        .tag-list { background: rgba(63, 131, 248, 0.15); color: #3f83f8; }

        /* Pulsante Dettagli Metodo */
        .btn-collapse {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 12px;
            color: #aaa;
            border: 1px solid #444;
            background: transparent;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .btn-collapse .chevron {
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

        .btn-collapse[aria-expanded="true"] .chevron {
            transform: rotate(-135deg);
            margin-bottom: -1px;
        }

        /* Tabelle e Strutture Annidate */
        .table-reflection {
            margin-bottom: 0;
            font-size: 0.85rem;
            border-top: 1px solid #34323c;
        }

        .table-reflection th {
            background-color: #1e1c24;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.65rem;
            padding: 10px 15px;
        }

        .table-reflection td { border-color: #34323c; padding: 10px 15px; }

        .dto-structure {
            padding: 1rem;
            border-left: 3px solid var(--accent-dto);
            background: rgba(227, 160, 8, 0.03);
            margin: 10px;
            border-radius: 0 8px 8px 0;
        }

        .list-structure {
            padding: 1rem;
            border-left: 3px solid var(--accent-list);
            background: rgba(63, 131, 248, 0.03);
            margin: 10px;
            border-radius: 0 8px 8px 0;
        }

        .prop-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        /* Utility */
        .badge-base { background-color: #3d3b47; color: #aaa; font-size: 0.6rem; }
        .badge-spec { background-color: rgba(7, 142, 110, 0.2); color: #10b981; font-size: 0.6rem; }
        .val-bool.true { color: #4ec9b0; }
        .val-bool.false { color: #f44747; }
        .val-num { color: #b5cea8; }
        .val-str { color: #ce9178; }
        code { background: #1e1e1e; padding: 2px 6px; border-radius: 4px; color: #d4d4d4; }
    </style>
</head>
<body>

<div class="container py-5">
    <header class="mb-5 text-center">
        <h1 class="fw-bold">API Reference</h1>
        <p class="text-muted">Documentazione gerarchica: Cartelle > Classi > Metodi</p>
    </header>

    <!-- Livello Radice -->
    <div class="tree-level">
        <div class="folder-node">
            <div class="folder-title" data-bs-toggle="collapse" data-bs-target="#content-api">
                Controllers / API
            </div>

            <div class="collapse show" id="content-api">
                <div class="tree-level">
                    <div class="folder-node">
                        <div class="folder-title" data-bs-toggle="collapse" data-bs-target="#content-token">
                            Token
                        </div>

                        <div class="collapse show" id="content-token">
                            <div class="tree-level">
                                
                                <!-- Esempio CLASSE 1: TokenController.php -->
                                <div class="class-node">
                                    <div class="class-title" data-bs-toggle="collapse" data-bs-target="#class-token-ctrl">
                                        TokenController.php
                                    </div>
                                    
                                    <div class="collapse show" id="class-token-ctrl">
                                        <!-- Metodo find() - VALUE -->
                                        <div class="api-card">
                                            <div class="api-header">
                                                <div><span class="text-muted small">public function</span> <span class="method-name">find()</span></div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="io-type-tag tag-value">Value: IntValue</span>
                                                    <button class="btn-collapse" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFind" aria-expanded="false">
                                                        Struttura <i class="chevron"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="collapse" id="collapseFind">
                                                <table class="table table-dark table-reflection">
                                                    <thead>
                                                        <tr><th style="width: 100px;">Scope</th><th>Proprietà</th><th>Valore</th><th>Classe</th></tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr><td><span class="badge badge-base">BASE</span></td><td><code>name</code></td><td><span class="val-str">"id"</span></td><td class="text-muted small">Solenoid\X\Input\Value</td></tr>
                                                        <tr><td><span class="badge badge-spec">SPECIFIC</span></td><td><code>min</code></td><td><span class="val-num">1</span></td><td class="text-muted small">Solenoid\X\Input\IntValue</td></tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Metodo update() - DTO -->
                                        <div class="api-card">
                                            <div class="api-header">
                                                <div><span class="text-muted small">public function</span> <span class="method-name">update()</span></div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="io-type-tag tag-dto">DTO: UpdateTokenDTO</span>
                                                    <button class="btn-collapse" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUpdate" aria-expanded="false">
                                                        Struttura <i class="chevron"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="collapse" id="collapseUpdate">
                                                <div class="dto-structure">
                                                    <div class="mb-3">
                                                        <div class="prop-row"><strong>label</strong> <span class="io-type-tag tag-value">StringValue</span></div>
                                                        <table class="table table-dark table-reflection mt-2">
                                                            <tbody>
                                                                <tr><td><span class="badge badge-spec">SPECIFIC</span></td><td><code>maxLength</code></td><td><span class="val-num">50</span></td></tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="mb-0">
                                                        <div class="prop-row"><strong>expires</strong> <span class="io-type-tag tag-value">IntValue</span></div>
                                                        <table class="table table-dark table-reflection mt-2">
                                                            <tbody>
                                                                <tr><td><span class="badge badge-base">BASE</span></td><td><code>required</code></td><td><span class="val-bool false">false</span></td></tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Esempio CLASSE 2: BatchController.php -->
                                <div class="class-node">
                                    <div class="class-title collapsed" data-bs-toggle="collapse" data-bs-target="#class-batch-ctrl">
                                        BatchController.php
                                    </div>
                                    
                                    <div class="collapse" id="class-batch-ctrl">
                                        <!-- Metodo delete() - ARRAYLIST -->
                                        <div class="api-card">
                                            <div class="api-header">
                                                <div><span class="text-muted small">public function</span> <span class="method-name">delete()</span></div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="io-type-tag tag-list">ArrayList&lt;IntValue&gt;</span>
                                                    <button class="btn-collapse" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDelete" aria-expanded="false">
                                                        Struttura <i class="chevron"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="collapse" id="collapseDelete">
                                                <div class="list-structure">
                                                    <div class="text-muted small mb-2">Validazione per ogni ID:</div>
                                                    <table class="table table-dark table-reflection">
                                                        <tbody>
                                                            <tr><td><span class="badge badge-spec">SPECIFIC</span></td><td><code>min</code></td><td><span class="val-num">1</span></td></tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>