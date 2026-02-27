<!DOCTYPE html>
<html lang="en" data-period="{{ $periodLabel }}" data-printed="{{ $printedOn }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report — SamgyHann 199</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 15px;
            color: #111827;
            background: #e5e7eb;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ══ TOOLBAR ══ */
        .toolbar {
            background: #111827; padding: 16px 48px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100; gap: 16px;
        }
        .toolbar-title { font-size: 15px; font-weight: 600; color: #d1d5db; }
        .btn-print {
            background: #dc2626; color: #fff; border: none;
            padding: 10px 24px; border-radius: 8px; font-size: 14px;
            font-weight: 700; cursor: pointer;
        }
        .btn-print:hover { background: #b91c1c; }

        /* ══ WRAPPER ══ */
        .wrap {
            max-width: 1060px; margin: 32px auto 56px;
            background: #fff; border-radius: 14px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.12); overflow: hidden;
        }

        /* ══ REPORT HEADER ══ */
        .rh {
            background: linear-gradient(135deg, #7f1d1d 0%, #dc2626 55%, #ea580c 100%);
            color: #fff; padding: 36px 48px 30px;
        }
        .rh-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; }
        .rh-brand { display: flex; align-items: center; gap: 16px; }
        .rh-logo {
            height: 60px; width: 60px; border-radius: 12px;
            background: rgba(255,255,255,0.2); object-fit: contain; padding: 4px;
        }
        .rh-name    { font-size: 28px; font-weight: 900; letter-spacing: -0.5px; }
        .rh-sub     { font-size: 12px; opacity: 0.75; margin-top: 4px; letter-spacing: 1px; text-transform: uppercase; }
        .rh-meta    { text-align: right; font-size: 13px; opacity: 0.85; line-height: 2.1; }
        .rh-period  {
            margin-top: 22px; padding-top: 18px;
            border-top: 1px solid rgba(255,255,255,0.2);
            font-size: 20px; font-weight: 800;
        }

        /* ══ TOC ══ */
        .toc {
            display: flex; flex-wrap: wrap; gap: 8px;
            padding: 20px 48px; background: #f9fafb; border-bottom: 1px solid #e5e7eb;
        }
        .toc a {
            font-size: 13px; font-weight: 600; padding: 6px 16px;
            border-radius: 20px; background: #fff; color: #374151;
            text-decoration: none; border: 1.5px solid #d1d5db; transition: all 0.15s;
        }
        .toc a:hover { background: #dc2626; color: #fff; border-color: #dc2626; }

        /* ══ SECTION TITLE ══ */
        .stitle {
            font-size: 11px; font-weight: 800; letter-spacing: 2px;
            text-transform: uppercase; color: #dc2626;
            margin: 40px 48px 20px; padding-bottom: 12px;
            border-bottom: 2px solid #fee2e2;
            display: flex; align-items: center; gap: 12px;
        }
        .stitle-bar {
            display: inline-block; width: 5px; height: 20px;
            background: #dc2626; border-radius: 3px; flex-shrink: 0;
        }

        /* ══ KPI GRID ══ */
        .kpis {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 14px; margin: 0 48px;
        }
        /* Summary section has 7 cards — force single row */
        .kpis.kpis-7 {
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }
        .kpis.kpis-7 .kpi {
            padding: 16px 8px;
        }
        .kpis.kpis-7 .kpi-val {
            font-size: 20px;
        }
        .kpis.kpis-7 .kpi-val.kpi-val--lg { font-size: 16px; }
        .kpis.kpis-7 .kpi-val.kpi-val--xl { font-size: 13px; }
        .kpi {
            background: #f9fafb; border: 2px solid #e5e7eb;
            border-radius: 12px; padding: 20px 14px; text-align: center;
            min-width: 0;
        }
        .kpi-lbl {
            font-size: 10.5px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.7px; color: #9ca3af; margin-bottom: 10px;
            line-height: 1.3;
        }
        .kpi-val  { font-size: 28px; font-weight: 900; color: #111827; line-height: 1.1; word-break: break-word; overflow-wrap: break-word; }
        .kpi-val.kpi-val--lg { font-size: 21px; }
        .kpi-val.kpi-val--xl { font-size: 16px; }
        .kpi-hint { font-size: 12px; color: #9ca3af; margin-top: 5px; }
        .green  { color: #16a34a; }
        .red    { color: #dc2626; }
        .orange { color: #ea580c; }
        .blue   { color: #2563eb; }
        .purple { color: #7c3aed; }

        /* ══ NO DATA ══ */
        .nodata {
            margin: 0 48px 24px; padding: 44px; text-align: center;
            background: #f9fafb; border: 2px dashed #d1d5db;
            border-radius: 12px; color: #9ca3af; font-size: 15px;
        }

        /* ══ TABLES ══ */
        .twrap { margin: 0 48px; border-radius: 10px; overflow: hidden; border: 1.5px solid #e5e7eb; }
        table  { width: 100%; border-collapse: collapse; font-size: 14px; }
        thead tr { background: #1f2937; }
        th {
            color: #e5e7eb; padding: 13px 16px;
            text-align: left; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px; white-space: nowrap;
        }
        th.r, td.r { text-align: right; }
        th.c, td.c { text-align: center; }
        td { padding: 13px 16px; border-bottom: 1px solid #f3f4f6; color: #374151; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:nth-child(even) td { background: #f9fafb; }
        tbody tr:hover td { background: #f0f9ff !important; }

        .tn  { font-weight: 700; color: #111827; font-size: 14px; }
        .tm  { color: #9ca3af; font-size: 13px; }
        .tg  { font-weight: 700; color: #16a34a; }
        .tr  { font-weight: 700; color: #dc2626; }
        .to  { font-weight: 700; color: #ea580c; }
        .tb  { font-weight: 700; color: #111827; }

        /* ══ BADGES ══ */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 700; white-space: nowrap;
        }
        .b-cash { background: #dcfce7; color: #166534; }
        .b-qr   { background: #dbeafe; color: #1e40af; }
        .b-in   { background: #dcfce7; color: #166534; }
        .b-out  { background: #fee2e2; color: #991b1b; }
        .b-same { background: #f3f4f6; color: #6b7280; }
        .b-low  { background: #fef9c3; color: #854d0e; }
        .b-ok   { background: #dcfce7; color: #166534; }
        .b-zero { background: #fee2e2; color: #991b1b; }

        /* ══ TWO COL ══ */
        .twocol { display: flex; gap: 24px; margin: 0 48px; }
        .twocol > div { flex: 1; min-width: 0; }
        .col-head {
            font-size: 12px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 1px; color: #6b7280;
            padding-bottom: 10px; margin-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
            display: flex; justify-content: space-between; align-items: center;
        }

        /* ══ PACKAGE BARS ══ */
        .pkg-row { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }
        .pkg-lbl { font-size: 14px; font-weight: 700; width: 76px; color: #374151; }
        .pkg-bar-bg { flex: 1; height: 18px; background: #f3f4f6; border-radius: 9px; overflow: hidden; }
        .pkg-bar    { height: 100%; border-radius: 9px; }
        .pb-basic   { background: #dc2626; }
        .pb-premium { background: #ea580c; }
        .pb-deluxe  { background: #d97706; }
        .pkg-ct { font-size: 13px; font-weight: 700; color: #374151; width: 64px; text-align: right; }

        /* ══ PEAK HOURS ══ */
        .peak-banner {
            display: inline-flex; align-items: center; gap: 10px;
            background: #fef2f2; color: #dc2626;
            font-size: 14px; font-weight: 700;
            padding: 10px 20px; border-radius: 10px;
            border: 2px solid #fecaca; margin: 0 48px 20px;
        }
        .peak-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(66px, 1fr));
            gap: 8px; margin: 0 48px 24px; align-items: end;
        }
        .peak-col   { display: flex; flex-direction: column; align-items: center; gap: 4px; }
        .peak-bwrap { width: 100%; height: 100px; display: flex; align-items: flex-end; }
        .peak-b     { width: 100%; border-radius: 6px 6px 0 0; min-height: 5px; }
        .pk-top  { background: #dc2626; }
        .pk-high { background: #ea580c; }
        .pk-mid  { background: #fbbf24; }
        .pk-low  { background: #d1d5db; }
        .peak-lbl { font-size: 11px; color: #6b7280; text-align: center; white-space: nowrap; }
        .peak-ct  { font-size: 11px; font-weight: 700; color: #374151; }

        /* ══ EXTRAS ══ */
        .ext-row {
            display: flex; align-items: center; gap: 14px;
            padding: 11px 0; border-bottom: 1px solid #f3f4f6;
        }
        .ext-row:last-child { border-bottom: none; }
        .ext-rank { width: 24px; font-size: 12px; font-weight: 700; color: #9ca3af; flex-shrink: 0; text-align: center; }
        .ext-name { flex: 1; font-size: 14px; font-weight: 600; color: #111827; }
        .ext-bwrap { width: 140px; height: 10px; background: #f3f4f6; border-radius: 5px; overflow: hidden; flex-shrink: 0; }
        .ext-bar   { height: 100%; border-radius: 5px; }
        .eb-red  { background: #dc2626; }
        .eb-blue { background: #2563eb; }
        .ext-qty { font-size: 13px; color: #9ca3af; width: 48px; text-align: right; flex-shrink: 0; }
        .ext-rev { font-size: 14px; font-weight: 700; color: #111827; width: 90px; text-align: right; flex-shrink: 0; }
        .ext-empty { padding: 24px 0; text-align: center; color: #9ca3af; font-size: 14px; }

        /* ══ DISCOUNT CARDS ══ */
        .disc-grid { display: flex; gap: 16px; margin: 0 48px; flex-wrap: wrap; }
        .disc-card {
            flex: 1; min-width: 170px;
            background: #f9fafb; border: 2px solid #e5e7eb;
            border-radius: 12px; padding: 22px 20px;
        }
        .disc-emoji  { font-size: 32px; margin-bottom: 10px; }
        .disc-type   { font-size: 13px; font-weight: 700; color: #6b7280; margin-bottom: 10px; }
        .disc-count  { font-size: 36px; font-weight: 900; line-height: 1; }
        .disc-amount { font-size: 14px; color: #6b7280; margin-top: 8px; font-weight: 500; }
        .disc-pct    { font-size: 12px; color: #9ca3af; margin-top: 4px; }
        .disc-note {
            margin: 18px 48px 0;
            padding: 16px 20px;
            background: #fffbeb; border: 2px solid #fde68a;
            border-radius: 10px; font-size: 13px; color: #92400e; line-height: 1.8;
        }

        /* ══ STOCK CARDS (the big change) ══ */
        .cat-head {
            font-size: 13px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 1.5px; color: #6b7280;
            margin: 20px 48px 12px; padding: 0;
        }
        .cat-head:first-of-type,
        .stitle + .kpis + div .cat-head:first-child,
        .kpis + .cat-head {
            margin-top: 0;
        }
        .stock-cards { display: flex; flex-direction: column; gap: 10px; margin: 0 48px 28px; }
        .stock-card {
            display: grid;
            grid-template-columns: 1fr 160px 40px 160px 120px 130px;
            align-items: center; gap: 0;
            background: #fff; border: 2px solid #e5e7eb;
            border-radius: 12px; overflow: hidden;
        }
        .stock-card:hover { border-color: #93c5fd; }
        .sc-name {
            padding: 16px 20px;
            font-size: 15px; font-weight: 700; color: #111827;
        }
        .sc-name span { display: block; font-size: 12px; font-weight: 500; color: #9ca3af; margin-top: 2px; }
        .sc-open {
            padding: 16px 20px; text-align: right;
            background: #f0fdf4; border-left: 1px solid #e5e7eb;
        }
        .sc-open-lbl  { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #16a34a; margin-bottom: 4px; }
        .sc-open-val  { font-size: 22px; font-weight: 800; color: #15803d; line-height: 1; }
        .sc-open-unit { font-size: 12px; color: #6b7280; margin-top: 3px; }
        .sc-arrow {
            padding: 16px 0; text-align: center;
            font-size: 22px; color: #9ca3af; background: #f9fafb;
            border-left: 1px solid #e5e7eb;
        }
        .sc-close {
            padding: 16px 20px; text-align: right;
            background: #eff6ff; border-left: 1px solid #e5e7eb;
        }
        .sc-close-lbl  { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #2563eb; margin-bottom: 4px; }
        .sc-close-val  { font-size: 22px; font-weight: 800; color: #1d4ed8; line-height: 1; }
        .sc-close-unit { font-size: 12px; color: #6b7280; margin-top: 3px; }
        .sc-change {
            padding: 16px 14px; text-align: center;
            border-left: 1px solid #e5e7eb; background: #f9fafb;
        }
        .sc-change-lbl { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #9ca3af; margin-bottom: 6px; }
        .sc-change-val { font-size: 18px; font-weight: 800; line-height: 1; }
        .sc-status {
            padding: 16px 14px; text-align: center;
            border-left: 1px solid #e5e7eb;
        }
        /* dimmed = no movement */
        .stock-card.dimmed { opacity: 0.5; }

        /* ══ TOTALS ROW ══ */
        tfoot tr td {
            background: #1f2937 !important;
            color: #f9fafb !important;
            font-weight: 800 !important;
            font-size: 13px !important;
            padding: 13px 16px !important;
            border-bottom: none !important;
        }
        tfoot tr td.tot-label { color: #9ca3af !important; font-size: 10px !important; letter-spacing: 1px; text-transform: uppercase; font-weight: 700 !important; }
        tfoot tr td.tot-hi    { color: #4ade80 !important; font-size: 15px !important; }
        tfoot tr td.tot-warn  { color: #fbbf24 !important; }

        /* ══ INVENTORY TABLE ══ */
        .inv-stock-val { font-size: 16px; font-weight: 800; }

        /* ══ FOOTER ══ */
        .rfooter {
            margin-top: 40px; padding: 20px 48px;
            background: #f9fafb; border-top: 2px solid #e5e7eb;
            display: flex; justify-content: space-between; align-items: center;
            font-size: 13px; color: #9ca3af;
        }
        .spacer { height: 32px; }
        .screen-only { display: block; }
        .print-only  { display: none; }

        /* ══ PRINT ══ */
        /*
         * Standard business report: A4, 15mm margins, 10pt body, 8pt labels.
         * All screen decorations (gradients, shadows, rounded corners, hover
         * states, large KPI numbers) are stripped. Content is compact but
         * completely readable at arm's length.
         */
        @media print {

            @page {
                size: A4 portrait;
                margin: 15mm 15mm 20mm 15mm;
            }

            /* ── Reset & base ── */
            *, *::before, *::after {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }

            html, body {
                background: #fff !important;
                font-family: Arial, Helvetica, sans-serif !important;
                font-size: 10pt !important;
                line-height: 1.4 !important;
                color: #000 !important;
            }
            body { position: relative !important; }

            /* ── Hide screen-only chrome ── */
            .toolbar, .toc { display: none !important; }

            /* ── Screen vs Print visibility ── */
            .screen-only { display: none !important; }
            .print-only  { display: block !important; }

            /* ── Hide the bar chart (peak hours visual) — wastes half a page ── */
            .peak-grid { display: none !important; }




            .wrap {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: 100% !important;
                overflow: visible !important;
                background: #fff !important;
            }

            /* ── Report header ── */
            .rh {
                background: #1a1a2e !important;
                color: #fff !important;
                padding: 10pt 12pt 8pt !important;
                margin-bottom: 8pt !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }
            .rh-name   { font-size: 16pt !important; font-weight: bold !important; }
            .rh-sub    { font-size: 8pt !important; opacity: 0.8 !important; }
            .rh-meta   { font-size: 8pt !important; line-height: 1.6 !important; text-align: right !important; }
            .rh-logo   { height: 36pt !important; width: 36pt !important; }
            .rh-period { font-size: 11pt !important; font-weight: bold !important; margin-top: 6pt !important; padding-top: 6pt !important; border-top: 1pt solid rgba(255,255,255,0.3) !important; }
            .rh > div:last-child { font-size: 8pt !important; margin-top: 6pt !important; padding-top: 6pt !important; gap: 24pt !important; }

            /* ── Section titles ── */
            .stitle {
                font-size: 8pt !important;
                font-weight: bold !important;
                text-transform: uppercase !important;
                letter-spacing: 1pt !important;
                color: #000 !important;
                margin: 10pt 0 5pt !important;
                padding-bottom: 3pt !important;
                border-bottom: 1pt solid #000 !important;
                break-after: avoid !important;
                page-break-after: avoid !important;
            }
            .stitle-bar { display: none !important; }

            /* ── Global margin reset (screen uses 48px side gutters) ── */
            .stitle, .kpis, .twocol, .twrap, .disc-grid, .peak-grid,
            .peak-banner, .disc-note, .cat-head, .stock-cards, .nodata,
            .inv-grand-total, .rfooter {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            /* ── KPI cards ── */
            .kpis {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 4pt !important;
                margin-bottom: 6pt !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }
            .kpis.kpis-7 { grid-template-columns: repeat(7, 1fr) !important; }
            .kpi {
                background: #f4f4f4 !important;
                border: 0.5pt solid #ccc !important;
                border-radius: 0 !important;
                padding: 5pt 4pt !important;
                text-align: center !important;
                break-inside: avoid !important;
            }
            .kpi-lbl  { font-size: 6.5pt !important; font-weight: bold !important; color: #555 !important; margin-bottom: 2pt !important; letter-spacing: 0 !important; }
            .kpi-val  { font-size: 13pt !important; font-weight: bold !important; color: #000 !important; line-height: 1.1 !important; }
            .kpis-7 .kpi-val { font-size: 10pt !important; }
            .kpi-val--lg { font-size: 9pt !important; }
            .kpi-val--xl { font-size: 8pt !important; }
            .kpi-hint { font-size: 7pt !important; color: #666 !important; }

            /* ── Tables ── */
            .twrap {
                border: 0.5pt solid #ccc !important;
                border-radius: 0 !important;
                margin-bottom: 8pt !important;
                overflow: visible !important;
            }
            table  { width: 100% !important; font-size: 8.5pt !important; border-collapse: collapse !important; }
            thead  { display: table-header-group !important; }
            tfoot  { display: table-footer-group !important; }
            thead tr, tfoot tr { background: #1a1a2e !important; }
            th {
                padding: 4pt 5pt !important;
                font-size: 7pt !important;
                font-weight: bold !important;
                background: #1a1a2e !important;
                color: #fff !important;
                white-space: nowrap !important;
                text-transform: uppercase !important;
                letter-spacing: 0.3pt !important;
            }
            td {
                padding: 4pt 5pt !important;
                border-bottom: 0.5pt solid #e0e0e0 !important;
                vertical-align: middle !important;
                color: #000 !important;
            }
            tbody tr:last-child td { border-bottom: none !important; }
            tbody tr:nth-child(even) td { background: #f9f9f9 !important; }
            tfoot tr td {
                padding: 4pt 5pt !important;
                font-size: 8pt !important;
                font-weight: bold !important;
                background: #1a1a2e !important;
                color: #fff !important;
                border-bottom: none !important;
            }
            tfoot tr td.tot-label { font-size: 7pt !important; color: #aaa !important; font-weight: normal !important; }
            tfoot tr td.tot-hi    { font-size: 10pt !important; color: #6ee7b7 !important; }
            tfoot tr td.tot-warn  { color: #fcd34d !important; }
            tr { break-inside: avoid !important; page-break-inside: avoid !important; }
            .tn { font-size: 8.5pt !important; font-weight: bold !important; }
            .tm { font-size: 8pt !important; color: #555 !important; }
            .inv-stock-val { font-size: 9pt !important; font-weight: bold !important; }

            /* ── Two-column layout ── */
            .twocol {
                display: flex !important;
                gap: 12pt !important;
                margin: 0 !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
                overflow: visible !important;
            }
            .twocol > div { overflow: visible !important; }
            .col-head {
                font-size: 7pt !important;
                font-weight: bold !important;
                text-transform: uppercase !important;
                padding-bottom: 4pt !important;
                margin-bottom: 5pt !important;
                border-bottom: 1pt solid #ccc !important;
            }

            /* ── Package bars ── */
            .pkg-row    { margin-bottom: 5pt !important; }
            .pkg-lbl    { font-size: 8.5pt !important; width: 52pt !important; }
            .pkg-bar-bg { height: 8pt !important; border-radius: 0 !important; }
            .pkg-ct     { font-size: 8.5pt !important; width: 36pt !important; }

            /* ── Peak hours ── */
            .peak-banner {
                font-size: 8pt !important;
                padding: 4pt 8pt !important;
                margin: 0 0 6pt !important;
                border-radius: 0 !important;
                background: #fff3f3 !important;
                border: 0.5pt solid #f99 !important;
            }
            .peak-grid  { gap: 4pt !important; margin: 0 0 8pt !important; }
            .peak-bwrap { height: 48pt !important; }
            .peak-lbl   { font-size: 6.5pt !important; }
            .peak-ct    { font-size: 6.5pt !important; font-weight: bold !important; }

            /* ── Extras ── */
            .ext-row  { padding: 3pt 0 !important; break-inside: avoid !important; }
            .ext-name { font-size: 8.5pt !important; }
            .ext-rev  { font-size: 8.5pt !important; width: 52pt !important; }
            .ext-qty  { font-size: 8pt !important; }
            .ext-bwrap { width: 52pt !important; }
            .ext-rank { font-size: 8pt !important; }

            /* ── Discount cards ── */
            .disc-grid {
                display: flex !important;
                gap: 6pt !important;
                flex-wrap: nowrap !important;
                margin: 0 0 6pt !important;
                break-inside: avoid !important;
            }
            .disc-card {
                flex: 1 !important;
                padding: 6pt 5pt !important;
                border: 0.5pt solid #ccc !important;
                border-radius: 0 !important;
                background: #f9f9f9 !important;
                break-inside: avoid !important;
            }
            .disc-emoji  { font-size: 14pt !important; margin-bottom: 2pt !important; }
            .disc-type   { font-size: 7pt !important; color: #555 !important; margin-bottom: 2pt !important; }
            .disc-count  { font-size: 18pt !important; font-weight: bold !important; }
            .disc-amount { font-size: 7pt !important; color: #555 !important; margin-top: 2pt !important; }
            .disc-pct    { font-size: 6.5pt !important; color: #777 !important; }
            .disc-note {
                font-size: 7.5pt !important;
                padding: 5pt 7pt !important;
                margin: 0 0 5pt !important;
                background: #fffbeb !important;
                border: 0.5pt solid #f0d080 !important;
                border-radius: 0 !important;
                line-height: 1.5 !important;
                break-inside: avoid !important;
            }

            /* ── Stock cards ── */
            .cat-head {
                font-size: 7.5pt !important;
                font-weight: bold !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5pt !important;
                color: #333 !important;
                margin: 7pt 0 3pt !important;
                break-after: avoid !important;
            }
            .stock-cards {
                gap: 3pt !important;
                margin: 0 0 8pt !important;
                break-inside: auto !important;
            }
            .stock-card {
                display: grid !important;
                grid-template-columns: 1fr 72pt 18pt 72pt 58pt 64pt !important;
                border: 0.5pt solid #ccc !important;
                border-radius: 0 !important;
                overflow: visible !important;
                break-inside: avoid !important;
            }
            .sc-name           { font-size: 8.5pt !important; font-weight: bold !important; padding: 5pt 6pt !important; }
            .sc-name span      { font-size: 7pt !important; color: #666 !important; }
            .sc-open, .sc-close, .sc-change, .sc-status, .sc-arrow {
                padding: 5pt 4pt !important;
            }
            .sc-open  { background: #f0fff4 !important; }
            .sc-close { background: #eff6ff !important; }
            .sc-open-lbl, .sc-close-lbl, .sc-change-lbl { font-size: 6pt !important; font-weight: bold !important; }
            .sc-open-val, .sc-close-val   { font-size: 10pt !important; font-weight: bold !important; }
            .sc-open-unit, .sc-close-unit { font-size: 6.5pt !important; }
            .sc-change-val { font-size: 9pt !important; font-weight: bold !important; }
            .sc-arrow      { font-size: 10pt !important; color: #999 !important; }

            /* ── Badges ── */
            .badge {
                padding: 1pt 4pt !important;
                font-size: 6.5pt !important;
                border-radius: 2pt !important;
                font-weight: bold !important;
            }
            .b-ok   { background: #d1fae5 !important; color: #065f46 !important; }
            .b-low  { background: #fef9c3 !important; color: #713f12 !important; }
            .b-zero { background: #fee2e2 !important; color: #7f1d1d !important; }
            .b-in   { background: #d1fae5 !important; color: #065f46 !important; }
            .b-out  { background: #fee2e2 !important; color: #7f1d1d !important; }
            .b-same { background: #f3f4f6 !important; color: #374151 !important; }
            .b-cash { background: #d1fae5 !important; color: #065f46 !important; }
            .b-qr   { background: #dbeafe !important; color: #1e3a5f !important; }

            /* ── Colour overrides ── */
            .tg { color: #065f46 !important; }
            .tr { color: #7f1d1d !important; }
            .to { color: #78350f !important; }
            .tb { color: #000 !important; }
            .tm { color: #555 !important; }
            .green  { color: #065f46 !important; }
            .red    { color: #7f1d1d !important; }
            .orange { color: #78350f !important; }
            .blue   { color: #1e3a5f !important; }
            .purple { color: #4c1d95 !important; }

            /* ── No data ── */
            .nodata {
                padding: 10pt !important;
                font-size: 9pt !important;
                border: 0.5pt dashed #ccc !important;
                border-radius: 0 !important;
                break-inside: avoid !important;
            }

            /* ── Inventory grand total ── */
            .inv-grand-total {
                margin: 0 0 8pt !important;
                border-radius: 0 !important;
                padding: 6pt 10pt !important;
                break-inside: avoid !important;
            }
            .inv-grand-total div:last-child { font-size: 13pt !important; }

            /* ── Document footer (rfooter) and signature block ── */
            .spacer  { height: 4pt !important; }
            .rfooter {
                margin-top: 6pt !important;
                padding: 5pt 0 !important;
                font-size: 7.5pt !important;
                border-top: 0.5pt solid #ccc !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            /* ── Grand total summary panel ── */
            .grand-total-banner { break-inside: avoid !important; }
            .sig-block { break-inside: avoid !important; }

            /* Ensure wrap doesn't clip content */
            .wrap { overflow: visible !important; }

            /* ── Page footer: fixed bar on every page ── */
            .pgfooter {
                display: block !important;
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                height: 12mm !important;
                border-top: 0.75pt solid #333 !important;
                background: #fff !important;
                padding: 0 15mm !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                font-size: 8pt !important;
                color: #555 !important;
                font-family: Arial, Helvetica, sans-serif !important;
            }
            .pgfooter-mid {
                font-weight: bold !important;
                color: #000 !important;
                font-size: 9pt !important;
            }
        }

        .pgfooter { display: none; }


    </style>
</head>
<body>

{{-- Toolbar --}}
<div class="toolbar">
    <span class="toolbar-title">📄 &nbsp;SamgyHann 199 · Sales Report · {{ $periodLabel }}</span>
    <button class="btn-print" onclick="window.print()">🖨️ &nbsp;Print / Save as PDF</button>
</div>

<div class="wrap">

    {{-- Header --}}
    <div class="rh">
        <div class="rh-top">
            <div class="rh-brand">
                <img src="{{ asset('samgyhann-logo.png') }}" alt="logo" class="rh-logo">
                <div>
                    <div class="rh-name">SamgyHann 199</div>
                    <div class="rh-sub">Unlimited Samgyeopsal &nbsp;·&nbsp; Olongapo City, Zambales</div>
                </div>
            </div>
            <div class="rh-meta">
                <div><strong>Generated:</strong> {{ $generatedAt }}</div>
                <div><strong>By:</strong> {{ $generatedBy }}</div>
                <div>SamgyHann POS System</div>
            </div>
        </div>
        <div class="rh-period">📋 &nbsp;Sales Report &nbsp;·&nbsp; {{ $periodLabel }}</div>
        <div style="display:flex;gap:32px;margin-top:14px;padding-top:14px;border-top:1px solid rgba(255,255,255,0.15);font-size:13px;">
            <div><span style="opacity:0.65;font-size:11px;text-transform:uppercase;letter-spacing:1px;display:block;margin-bottom:3px;">📅 Beginning Date</span><strong>{{ $reportStartDate }}</strong></div>
            <div><span style="opacity:0.65;font-size:11px;text-transform:uppercase;letter-spacing:1px;display:block;margin-bottom:3px;">📅 Ending Date</span><strong>{{ $reportEndDate }}</strong></div>
            <div style="margin-left:auto;text-align:right;"><span style="opacity:0.65;font-size:11px;text-transform:uppercase;letter-spacing:1px;display:block;margin-bottom:3px;">🖨️ Printed On</span><strong>{{ $printedOn }}</strong></div>
        </div>
    </div>

    {{-- TOC --}}
    <div class="toc">
        <a href="#s1">① Summary</a>
        <a href="#s2">② Breakdown</a>
        @if($revenueByDay->count() > 1)<a href="#s3">③ Daily Revenue</a>@endif
        <a href="#s4">④ Peak Hours</a>
        <a href="#s5">⑤ Extras & Add-ons</a>
        <a href="#s6">⑥ Discounts</a>
        <a href="#s7">⑦ Order Details</a>
        <a href="#s8">⑧ Opening vs Closing Stock</a>
        <a href="#s9">⑨ Inventory</a>
    </div>

    {{-- ① SUMMARY --}}
    <div class="stitle" id="s1"><span class="stitle-bar"></span> ① Summary</div>

    @if((int)$summary->total_orders === 0)
        <div class="nodata">📭 &nbsp;No completed orders found for this period.</div>
    @else

    <div class="kpis kpis-7">
        @php
            if (!function_exists('kpiSizeClass')) {
                function kpiSizeClass(string $val): string {
                    $len = strlen($val);
                    if ($len > 11) return 'kpi-val--xl';
                    if ($len > 8)  return 'kpi-val--lg';
                    return '';
                }
            }
            $revStr  = '₱' . number_format($summary->total_revenue, 2);
            $avgStr  = '₱' . number_format($summary->avg_order, 2);
            $rpgStr  = '₱' . number_format($revenuePerGuest, 2);
            $discStr = '₱' . number_format($summary->total_discounts, 2);
        @endphp
        <div class="kpi"><div class="kpi-lbl">Total Orders</div><div class="kpi-val blue">{{ number_format($summary->total_orders) }}</div></div>
        <div class="kpi"><div class="kpi-lbl">Total Revenue</div><div class="kpi-val green {{ kpiSizeClass($revStr) }}">{{ $revStr }}</div></div>
        <div class="kpi"><div class="kpi-lbl">Total Guests</div><div class="kpi-val orange">{{ number_format($summary->total_guests) }}</div></div>
        <div class="kpi"><div class="kpi-lbl">Avg Order Value</div><div class="kpi-val {{ kpiSizeClass($avgStr) }}">{{ $avgStr }}</div><div class="kpi-hint">per order</div></div>
        <div class="kpi"><div class="kpi-lbl">Revenue / Guest</div><div class="kpi-val purple {{ kpiSizeClass($rpgStr) }}">{{ $rpgStr }}</div><div class="kpi-hint">per head</div></div>
        <div class="kpi"><div class="kpi-lbl">Avg Party Size</div><div class="kpi-val">{{ number_format($summary->avg_party_size, 1) }}</div><div class="kpi-hint">guests / order</div></div>
        <div class="kpi"><div class="kpi-lbl">Total Discounts</div><div class="kpi-val red {{ kpiSizeClass($discStr) }}">{{ $discStr }}</div></div>
    </div>

    {{-- ② BREAKDOWN --}}
    <div class="stitle" id="s2"><span class="stitle-bar"></span> ② Breakdown</div>

    <div class="twocol">
        <div>
            <div class="col-head">💳 Payment Methods</div>
            <div class="twrap" style="margin:0;">
                <table>
                    <thead><tr>
                        <th>Method</th><th class="c">Orders</th><th class="r">Revenue</th><th class="r">Share</th>
                    </tr></thead>
                    <tbody>
                        @forelse($paymentBreakdown as $p)
                        @php $pct = $summary->total_revenue > 0 ? round(($p->total / $summary->total_revenue) * 100, 1) : 0; @endphp
                        <tr>
                            <td><span class="badge {{ $p->payment === 'Cash' ? 'b-cash' : 'b-qr' }}">{{ $p->payment === 'Cash' ? '💵' : '📱' }} {{ $p->payment }}</span></td>
                            <td class="c tb">{{ $p->count }}</td>
                            <td class="r tb">₱{{ number_format($p->total, 2) }}</td>
                            <td class="r tm">{{ $pct }}%</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="tm" style="text-align:center;padding:24px;">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>
            <div class="col-head">📦 Package Popularity <span style="font-weight:500;color:#9ca3af;">by guests</span></div>
            @php $maxPkg = max(array_values($packageCounts) ?: [1]); @endphp
            <div style="background:#f9fafb;border:2px solid #e5e7eb;border-radius:10px;padding:20px 18px;">
                @foreach($packageCounts as $name => $count)
                <div class="pkg-row">
                    <div class="pkg-lbl">{{ $name }}</div>
                    <div class="pkg-bar-bg">
                        <div class="pkg-bar pb-{{ strtolower($name) }}" style="width:{{ $maxPkg > 0 ? round(($count/$maxPkg)*100) : 0 }}%;"></div>
                    </div>
                    <div class="pkg-ct">{{ number_format($count) }} pax</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ③ DAILY REVENUE --}}
    @if($revenueByDay->count() > 1)
    <div class="stitle" id="s3"><span class="stitle-bar"></span> ③ Daily Revenue</div>
    <div class="twrap">
        <table>
            <thead><tr>
                <th>Date</th><th class="c">Orders</th><th class="c">Guests</th>
                <th class="r">Revenue</th><th class="r">Avg / Order</th><th class="r">Rev / Guest</th>
            </tr></thead>
            <tbody>
                @foreach($revenueByDay as $day)
                <tr>
                    <td class="tn">{{ \Carbon\Carbon::parse($day->day)->format('D, M j, Y') }}</td>
                    <td class="c">{{ $day->orders }}</td>
                    <td class="c">{{ $day->guests ?? 0 }}</td>
                    <td class="r tb">₱{{ number_format($day->revenue, 2) }}</td>
                    <td class="r tm">₱{{ $day->orders > 0 ? number_format($day->revenue / $day->orders, 2) : '—' }}</td>
                    <td class="r tm">₱{{ ($day->guests ?? 0) > 0 ? number_format($day->revenue / $day->guests, 2) : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
            @php
                $dayTotalOrders  = $revenueByDay->sum('orders');
                $dayTotalGuests  = $revenueByDay->sum('guests');
                $dayTotalRevenue = $revenueByDay->sum('revenue');
            @endphp
            <tfoot><tr>
                <td class="tot-label">{{ $revenueByDay->count() }}-day total</td>
                <td class="c">{{ number_format($dayTotalOrders) }}</td>
                <td class="c">{{ number_format($dayTotalGuests) }}</td>
                <td class="r tot-hi">₱{{ number_format($dayTotalRevenue, 2) }}</td>
                <td class="r tot-warn">₱{{ $dayTotalOrders > 0 ? number_format($dayTotalRevenue / $dayTotalOrders, 2) : '—' }}</td>
                <td class="r tot-warn">₱{{ $dayTotalGuests > 0 ? number_format($dayTotalRevenue / $dayTotalGuests, 2) : '—' }}</td>
            </tr></tfoot>
        </table>
    </div>
    @endif

    {{-- ④ PEAK HOURS --}}
    <div class="stitle" id="s4"><span class="stitle-bar"></span> ④ Peak Hours Analysis</div>

    @if($peakHours->isEmpty())
        <div class="nodata">No hourly data available for this period.</div>
    @else
        @if($busiestHour)
        <div class="peak-banner">
            🔥 &nbsp;Busiest hour: &nbsp;<strong>{{ $busiestHour['label'] }}</strong>
            &nbsp;·&nbsp; {{ $busiestHour['orders'] }} orders
            &nbsp;·&nbsp; ₱{{ number_format($busiestHour['revenue'], 2) }} revenue
        </div>
        @endif

        <div class="peak-grid">
            @foreach($peakHours as $hour)
            @php
                $pct = $maxHourRevenue > 0 ? round(($hour['revenue']/$maxHourRevenue)*100) : 0;
                $cls = $pct >= 85 ? 'pk-top' : ($pct >= 55 ? 'pk-high' : ($pct >= 25 ? 'pk-mid' : 'pk-low'));
            @endphp
            <div class="peak-col">
                <div class="peak-ct">{{ $hour['orders'] }}</div>
                <div class="peak-bwrap"><div class="peak-b {{ $cls }}" style="height:{{ max(5,$pct) }}%;"></div></div>
                <div class="peak-lbl">{{ $hour['label'] }}</div>
            </div>
            @endforeach
        </div>

        <div class="twrap">
            <table>
                <thead><tr>
                    <th>Hour</th><th class="c">Orders</th><th class="c">Guests</th>
                    <th class="r">Revenue</th><th class="r">Avg / Order</th>
                </tr></thead>
                <tbody>
                    @foreach($peakHours->sortByDesc('revenue') as $hour)
                    <tr>
                        <td class="tn">{{ $hour['label'] }}</td>
                        <td class="c">{{ $hour['orders'] }}</td>
                        <td class="c">{{ $hour['guests'] }}</td>
                        <td class="r tb">₱{{ number_format($hour['revenue'], 2) }}</td>
                        <td class="r tm">₱{{ $hour['orders'] > 0 ? number_format($hour['revenue']/$hour['orders'],2) : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ⑤ EXTRAS & ADD-ONS --}}
    <div class="stitle" id="s5"><span class="stitle-bar"></span> ⑤ Extras & Add-ons Revenue</div>

    <div class="twocol">
        <div>
            <div class="col-head">🛒 Extra Items
                @if($totalExtrasRevenue > 0)<span class="tg">₱{{ number_format($totalExtrasRevenue, 2) }}</span>@endif
            </div>
            @php $mxE = collect($extrasMap)->max('revenue') ?: 1; $rk = 0; @endphp
            @if(empty($extrasMap))
                <div class="ext-empty">No extra items sold.</div>
            @else
                @foreach($extrasMap as $name => $d)
                @php $rk++; $bw = round(($d['revenue']/$mxE)*100); @endphp
                <div class="ext-row">
                    <div class="ext-rank">#{{ $rk }}</div>
                    <div class="ext-name">{{ $name }}</div>
                    <div class="ext-bwrap"><div class="ext-bar eb-red" style="width:{{ $bw }}%;"></div></div>
                    <div class="ext-qty">{{ $d['qty'] }}×</div>
                    <div class="ext-rev">₱{{ number_format($d['revenue'], 2) }}</div>
                </div>
                @endforeach
            @endif
        </div>
        <div>
            <div class="col-head">➕ Add-ons
                @if($totalAddonsRevenue > 0)<span class="tg">₱{{ number_format($totalAddonsRevenue, 2) }}</span>@endif
            </div>
            @php $mxA = collect($addonsMap)->max('revenue') ?: 1; $rk = 0; @endphp
            @if(empty($addonsMap))
                <div class="ext-empty">No add-ons sold.</div>
            @else
                @foreach($addonsMap as $name => $d)
                @php $rk++; $bw = round(($d['revenue']/$mxA)*100); @endphp
                <div class="ext-row">
                    <div class="ext-rank">#{{ $rk }}</div>
                    <div class="ext-name">{{ $name }}</div>
                    <div class="ext-bwrap"><div class="ext-bar eb-blue" style="width:{{ $bw }}%;"></div></div>
                    <div class="ext-qty">{{ $d['qty'] }}×</div>
                    <div class="ext-rev">₱{{ number_format($d['revenue'], 2) }}</div>
                </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- ⑥ DISCOUNTS --}}
    <div class="stitle" id="s6"><span class="stitle-bar"></span> ⑥ Discount Summary</div>

    @if($totalDiscountedGuests === 0)
        <div class="nodata">No discounts applied this period.</div>
    @else
    <div class="disc-grid">
        @foreach($discountTypes as $dt)
        @php $pg = $summary->total_guests > 0 ? round(($dt['count']/$summary->total_guests)*100,1) : 0; @endphp
        <div class="disc-card">
            <div class="disc-emoji">{{ $dt['emoji'] }}</div>
            <div class="disc-type">{{ $dt['label'] }}</div>
            <div class="disc-count" style="color:{{ $dt['color'] }};">{{ number_format($dt['count']) }}</div>
            <div class="disc-amount">₱{{ number_format($dt['amount'], 2) }} saved</div>
            <div class="disc-pct">{{ $pg }}% of all guests</div>
        </div>
        @endforeach
        <div class="disc-card" style="background:#fefce8;border-color:#fde68a;">
            <div class="disc-emoji">🧾</div>
            <div class="disc-type">Total Discounted</div>
            <div class="disc-count">{{ number_format($totalDiscountedGuests) }}</div>
            <div class="disc-amount">₱{{ number_format($summary->total_discounts, 2) }} total</div>
            <div class="disc-pct">across all types</div>
        </div>
    </div>
    <div class="disc-note">
        ⚠️ <strong>Compliance note:</strong> SC and PWD discounts are mandated under Philippine law (RA 9994 & RA 7277).
        Retain this report for BIR / DTI documentation.
    </div>
    @endif

    {{-- ⑦ ORDER DETAILS --}}
    @if($orders->count() > 0)
    <div class="stitle" id="s7">
        <span class="stitle-bar"></span> ⑦ Order Details
        <span style="font-size:12px;font-weight:500;color:#9ca3af;text-transform:none;letter-spacing:0;">({{ $orders->count() }} orders)</span>
    </div>
    <div class="twrap">
        <table>
            <thead><tr>
                <th>Receipt</th><th>Date & Time</th><th class="c">Guests</th>
                <th>Packages</th><th class="c">Payment</th>
                <th class="r">Subtotal</th><th class="c">Disc</th><th class="r">Total</th>
            </tr></thead>
            <tbody>
                @foreach($orders as $o)
                <tr>
                    <td class="tn">#{{ $o->receipt_number }}</td>
                    <td class="tm">{{ $o->completed_at->format('M j, Y · g:i A') }}</td>
                    <td class="c">{{ $o->total_people }}</td>
                    <td class="tm" style="font-size:12px;">
                        @if(!empty($o->packages))
                            @foreach($o->packages as $pk){{ $pk['people'] }}×{{ $pk['name'] }}{{ !$loop->last?', ':'' }}@endforeach
                        @else—@endif
                    </td>
                    <td class="c"><span class="badge {{ $o->payment==='Cash'?'b-cash':'b-qr' }}">{{ $o->payment }}</span></td>
                    <td class="r tm">₱{{ number_format($o->subtotal,2) }}</td>
                    <td class="c">
                        @if($o->discount_percent > 0)<span class="tr">{{ $o->discount_percent }}%</span>
                        @else<span class="tm">—</span>@endif
                    </td>
                    <td class="r tb">₱{{ number_format($o->total,2) }}</td>
                </tr>
                @endforeach
            </tbody>
            @php
                $grandSubtotal  = $orders->sum('subtotal');
                $grandDiscounts = $orders->sum('discount_amount');
                $grandTotal     = $orders->sum('total');
                $grandGuests    = $orders->sum('total_people');
            @endphp
            <tfoot><tr>
                <td class="tot-label">Grand Total</td>
                <td class="tm" style="color:#9ca3af;font-size:11px;font-weight:600;">—</td>
                <td class="c" style="color:#f9fafb;font-weight:700;">{{ number_format($grandGuests) }}</td>
                <td style="color:#9ca3af;font-size:11px;">—</td>
                <td class="c" style="color:#9ca3af;font-size:11px;">{{ $orders->count() }} orders</td>
                <td class="r tot-warn">₱{{ number_format($grandSubtotal, 2) }}</td>
                <td class="c tot-warn">₱{{ number_format($grandDiscounts, 2) }}</td>
                <td class="r tot-hi">₱{{ number_format($grandTotal, 2) }}</td>
            </tr></tfoot>
        </table>
    </div>
    @endif

    @endif {{-- end no orders --}}

    {{-- ⑧ OPENING VS CLOSING STOCK --}}
    <div class="stitle" id="s8"><span class="stitle-bar"></span> ⑧ Opening vs Closing Stock</div>

    @php
        $byCategory   = collect($stockSnapshot)->groupBy('category');
        $changedCount = collect($stockSnapshot)->filter(fn($r) => $r['had_movements'])->count();
    @endphp

    <div class="kpis" style="margin-bottom:28px;">
        <div class="kpi"><div class="kpi-lbl">Products Tracked</div><div class="kpi-val blue">{{ count($stockSnapshot) }}</div></div>
        <div class="kpi"><div class="kpi-lbl">Had Changes</div><div class="kpi-val {{ $changedCount > 0 ? 'orange' : 'green' }}">{{ $changedCount }}</div></div>
        <div class="kpi"><div class="kpi-lbl">No Changes</div><div class="kpi-val">{{ count($stockSnapshot) - $changedCount }}</div></div>
    </div>

    @if(empty($stockSnapshot))
        <div class="nodata">No products found.</div>
    @else
        {{-- ── Screen view: visual cards ── --}}
        <div class="screen-only">
            @foreach($byCategory as $category => $items)
            <div class="cat-head">{{ $category }}</div>
            <div class="stock-cards">
                @foreach($items as $row)
                @php
                    $net    = $row['net_change'];
                    $pfx    = $net > 0 ? '+' : '';
                    $netCls = $net > 0 ? 'tg' : ($net < 0 ? 'tr' : 'tm');
                    $dimmed = !$row['had_movements'] ? 'dimmed' : '';
                @endphp
                <div class="stock-card {{ $dimmed }}">
                    <div class="sc-name">{{ $row['name'] }}<span>{{ $row['unit'] }}</span></div>
                    <div class="sc-open">
                        <div class="sc-open-lbl">🌅 Opening</div>
                        <div class="sc-open-val">{{ number_format($row['opening_stock'], 2) }}</div>
                        <div class="sc-open-unit">{{ $row['unit'] }}</div>
                    </div>
                    <div class="sc-arrow">→</div>
                    <div class="sc-close">
                        <div class="sc-close-lbl">🌇 Closing</div>
                        <div class="sc-close-val">{{ number_format($row['closing_stock'], 2) }}</div>
                        <div class="sc-close-unit">{{ $row['unit'] }}</div>
                    </div>
                    <div class="sc-change">
                        <div class="sc-change-lbl">Change</div>
                        @if($row['had_movements'])
                            <div class="sc-change-val {{ $netCls }}">{{ $pfx }}{{ number_format($net, 2) }}</div>
                        @else
                            <div class="sc-change-val tm">—</div>
                        @endif
                    </div>
                    <div class="sc-status">
                        @if(!$row['had_movements'])<span class="badge b-same">No change</span>
                        @elseif($net < 0)<span class="badge b-out">▼ Used</span>
                        @elseif($net > 0)<span class="badge b-in">▲ Restocked</span>
                        @else<span class="badge b-same">Balanced</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>

        {{-- ── Print view: compact table ── --}}
        <div class="print-only">
            <div class="twrap">
                <table>
                    <thead><tr>
                        <th>Item</th>
                        <th class="c">Unit</th>
                        <th class="r">Opening</th>
                        <th class="r">Closing</th>
                        <th class="r">Change</th>
                        <th class="c">Status</th>
                    </tr></thead>
                    <tbody>
                        @foreach(collect($stockSnapshot)->groupBy('category') as $category => $rows)
                        <tr class="print-cat-row">
                            <td colspan="6" style="background:#1f2937;color:#e5e7eb;font-weight:700;font-size:7pt;text-transform:uppercase;letter-spacing:0.5pt;padding:3pt 5pt;">{{ $category }}</td>
                        </tr>
                        @foreach($rows as $row)
                        @php
                            $net    = $row['net_change'];
                            $pfx    = $net > 0 ? '+' : '';
                            $netCls = $net > 0 ? 'tg' : ($net < 0 ? 'tr' : 'tm');
                        @endphp
                        <tr>
                            <td class="tn">{{ $row['name'] }}</td>
                            <td class="c tm">{{ $row['unit'] }}</td>
                            <td class="r">{{ number_format($row['opening_stock'], 2) }}</td>
                            <td class="r tb">{{ number_format($row['closing_stock'], 2) }}</td>
                            <td class="r {{ $netCls }}">{{ $row['had_movements'] ? $pfx.number_format($net,2) : '—' }}</td>
                            <td class="c">
                                @if(!$row['had_movements'])<span class="badge b-same">No change</span>
                                @elseif($net < 0)<span class="badge b-out">▼ Used</span>
                                @elseif($net > 0)<span class="badge b-in">▲ Restocked</span>
                                @else<span class="badge b-same">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ⑨ INVENTORY SNAPSHOT --}}
    <div class="stitle" id="s9"><span class="stitle-bar"></span> ⑨ Inventory Snapshot</div>

    <div class="kpis" style="margin-bottom:24px;">
        <div class="kpi"><div class="kpi-lbl">Total SKUs</div><div class="kpi-val blue">{{ $inventory->count() }}</div></div>
        <div class="kpi"><div class="kpi-lbl">Low Stock</div><div class="kpi-val {{ $lowStockCount > 0 ? 'orange' : 'green' }}">{{ $lowStockCount }}</div></div>
        <div class="kpi"><div class="kpi-lbl">Out of Stock</div><div class="kpi-val {{ $outOfStockCount > 0 ? 'red' : 'green' }}">{{ $outOfStockCount }}</div></div>
        <div class="kpi"><div class="kpi-lbl">Est. Inventory Value</div>
            @php $invValStr = '₱' . number_format($totalInventoryValue, 2);
                 $invValCls = strlen($invValStr) > 12 ? 'kpi-val--xl' : (strlen($invValStr) > 9 ? 'kpi-val--lg' : '');
            @endphp
            <div class="kpi-val green {{ $invValCls }}">{{ $invValStr }}</div>
        </div>
    </div>

    @if($inventory->isEmpty())
        <div class="nodata">No inventory items found.</div>
    @else
        @foreach($inventoryByCategory as $category => $items)
        @php $catValue = $items->sum(fn($i) => (float)$i->stock * (float)$i->cost); @endphp
        <div class="cat-head">{{ $category }}</div>
        <div class="twrap" style="margin-bottom:24px;">
            <table>
                <thead><tr>
                    <th>Item</th>
                    <th class="r">Current Stock</th>
                    <th class="r" style="background:#1a3a2a;color:#4ade80;">Stocks Left<br><span style="font-size:9px;opacity:0.8;">End of Day</span></th>
                    <th class="c">Unit</th>
                    <th class="r" style="background:#1a2a3a;color:#60a5fa;">Order Point<br><span style="font-size:9px;opacity:0.8;">Reorder At</span></th>
                    <th class="r">Cost / Unit</th>
                    <th class="r">Total Value</th>
                    <th class="c">Status</th>
                </tr></thead>
                <tbody>
                    @foreach($items as $item)
                    @php
                        $oos      = (float)$item->stock <= 0;
                        $low      = !$oos && $item->is_low_stock;
                        $slabel   = $oos ? 'Out of Stock' : ($low ? 'Low Stock' : 'OK');
                        $sbadge   = $oos ? 'b-zero' : ($low ? 'b-low' : 'b-ok');
                        $scls     = $oos ? 'tr' : ($low ? 'to' : 'tg');
                        $ival     = (float)$item->stock * (float)$item->cost;
                        // Find closing stock from snapshot for this item
                        $snapRow     = collect($stockSnapshot)->firstWhere('name', $item->name);
                        $closingStk  = $snapRow ? $snapRow['closing_stock'] : (float)$item->stock;
                        $closingCls  = $closingStk <= 0 ? 'tr' : (($item->reorder_level > 0 && $closingStk <= (float)$item->reorder_level) ? 'to' : 'tg');
                    @endphp
                    <tr>
                        <td class="tn">{{ $item->name }}</td>
                        <td class="r inv-stock-val {{ $scls }}">{{ number_format((float)$item->stock, 2) }}</td>
                        <td class="r inv-stock-val {{ $closingCls }}" style="background:#f0fdf4;">{{ number_format($closingStk, 2) }}</td>
                        <td class="c tm">{{ $item->unit }}</td>
                        <td class="r" style="background:#eff6ff;font-weight:700;color:#1d4ed8;">{{ number_format((float)$item->reorder_level, 2) }}</td>
                        <td class="r tm">₱{{ number_format((float)$item->cost, 2) }}</td>
                        <td class="r tb">₱{{ number_format($ival, 2) }}</td>
                        <td class="c"><span class="badge {{ $sbadge }}">{{ $slabel }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot><tr>
                    <td class="tot-label" colspan="7">{{ $items->count() }} items · {{ $category }} subtotal</td>
                    <td class="r tot-hi">₱{{ number_format($catValue, 2) }}</td>
                </tr></tfoot>
            </table>
        </div>
        @endforeach

        {{-- Overall inventory grand total banner --}}
        <div class="inv-grand-total" style="margin: 0 48px 28px; background: #1f2937; border-radius: 10px; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <div style="color:#9ca3af; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:1.5px;">
                📦 &nbsp;Grand Total Inventory Value
            </div>
            <div style="color:#4ade80; font-size:24px; font-weight:900;">
                ₱{{ number_format($totalInventoryValue, 2) }}
            </div>
        </div>
    @endif

    {{-- Document Footer --}}
    <div class="spacer"></div>

    @php
        $grandSubtotalFtr  = isset($orders) ? $orders->sum('subtotal')       : 0;
        $grandDiscountsFtr = isset($orders) ? $orders->sum('discount_amount') : 0;
        $grandTotalFtr     = isset($orders) ? $orders->sum('total')           : 0;
        $grandGuestsFtr    = isset($orders) ? $orders->sum('total_people')    : 0;
        $orderCountFtr     = isset($orders) ? $orders->count()                : 0;
    @endphp

    {{-- Page Totals & Grand Total Banner --}}
    <div style="margin:0 48px 28px;border:2px solid #1f2937;border-radius:12px;overflow:hidden;">
        <div style="background:#1f2937;padding:12px 20px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:#9ca3af;">
            📊 &nbsp;Page Totals &amp; Grand Total
        </div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);background:#fff;">
            <div style="padding:16px 20px;border-right:1px solid #e5e7eb;">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#9ca3af;margin-bottom:6px;">Total Orders</div>
                <div style="font-size:26px;font-weight:900;color:#2563eb;">{{ number_format($orderCountFtr) }}</div>
            </div>
            <div style="padding:16px 20px;border-right:1px solid #e5e7eb;">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#9ca3af;margin-bottom:6px;">Total Guests</div>
                <div style="font-size:26px;font-weight:900;color:#7c3aed;">{{ number_format($grandGuestsFtr) }}</div>
            </div>
            <div style="padding:16px 20px;border-right:1px solid #e5e7eb;">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#9ca3af;margin-bottom:6px;">Total Discounts</div>
                <div style="font-size:22px;font-weight:900;color:#dc2626;">₱{{ number_format($grandDiscountsFtr, 2) }}</div>
            </div>
            <div style="padding:16px 20px;background:#f0fdf4;">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#166534;margin-bottom:6px;">🏆 Grand Total Revenue</div>
                <div style="font-size:22px;font-weight:900;color:#16a34a;">₱{{ number_format($grandTotalFtr, 2) }}</div>
                <div style="font-size:11px;color:#9ca3af;margin-top:4px;">Before discounts: ₱{{ number_format($grandSubtotalFtr, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Signature Block --}}
    <div class="rfooter">
        <div style="flex:1;">
            <div style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:#9ca3af;margin-bottom:4px;">SamgyHann 199 POS &nbsp;·&nbsp; Olongapo City, Zambales</div>
            <div style="font-size:11px;color:#6b7280;">Period: {{ $periodLabel }}</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;font-size:11px;color:#6b7280;">
            <div><strong style="color:#374151;">🖨️ Printed On:</strong> &nbsp;{{ $printedOn }}</div>
            <div><strong style="color:#374151;">📋 Effectivity Date:</strong> &nbsp;{{ $reportStartDate }} – {{ $reportEndDate }}</div>
        </div>
    </div>

    {{-- Prepared By / Received By signature lines --}}
    <div style="margin:0 48px 40px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:32px;">
        <div>
            <div style="border-top:2px solid #374151;padding-top:10px;margin-top:40px;">
                <div style="font-size:12px;font-weight:700;color:#111827;">{{ $generatedBy }}</div>
                <div style="font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#9ca3af;margin-top:3px;">Prepared By</div>
            </div>
        </div>
        <div>
            <div style="border-top:2px solid #d1d5db;padding-top:10px;margin-top:40px;">
                <div style="font-size:12px;font-weight:700;color:#6b7280;">&nbsp;</div>
                <div style="font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#9ca3af;margin-top:3px;">Received By</div>
            </div>
        </div>
        <div>
            <div style="border-top:2px solid #d1d5db;padding-top:10px;margin-top:40px;">
                <div style="font-size:12px;font-weight:700;color:#6b7280;">&nbsp;</div>
                <div style="font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#9ca3af;margin-top:3px;">Noted By / Approved By</div>
            </div>
        </div>
    </div>


</div>{{-- /.wrap --}}

{{-- Page footer: fixed bar repeated on every printed page --}}
<div class="pgfooter">
    <span>SamgyHann 199 &nbsp;·&nbsp; Olongapo City, Zambales &nbsp;·&nbsp; {{ $periodLabel }}</span>
    <span>Printed: {{ $printedOn }}</span>
</div>

</body>
</html>
