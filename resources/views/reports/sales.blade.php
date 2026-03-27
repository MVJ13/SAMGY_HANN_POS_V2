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
            font-size: 14px;
            color: #111827;
            background: #F3F4F6;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* ══ TOOLBAR (screen only) ══ */
        .toolbar {
            background: #1F2937;
            padding: 12px 32px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100; gap: 16px;
            border-bottom: 3px solid #D4451A;
        }
        .toolbar-title { font-size: 14px; font-weight: 600; color: #D1D5DB; }
        .toolbar-title strong { color: #FFFFFF; }
        .btn-print {
            background: #D4451A; color: #fff; border: none;
            padding: 9px 24px; border-radius: 6px; font-size: 13px;
            font-weight: 700; cursor: pointer; font-family: inherit;
            display: flex; align-items: center; gap: 6px;
        }
        .btn-print:hover { background: #A83614; }

        /* ══ WRAPPER ══ */
        .wrap {
            max-width: 1060px; margin: 24px auto 56px;
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        /* ══ REPORT HEADER ══ */
        .rh {
            background: #1F2937;
            color: #fff;
            padding: 24px 32px 20px;
            border-bottom: 3px solid #D4451A;
        }
        .rh-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; }
        .rh-brand { display: flex; align-items: center; gap: 12px; }
        .rh-logo {
            height: 48px; width: 48px; border-radius: 8px;
            object-fit: contain; padding: 3px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .rh-name { font-size: 22px; font-weight: 800; color: #fff; letter-spacing: -0.3px; }
        .rh-sub  { font-size: 11px; color: rgba(255,255,255,0.5); margin-top: 2px; letter-spacing: 0.8px; text-transform: uppercase; }
        .rh-meta { text-align: right; font-size: 12px; color: rgba(255,255,255,0.65); line-height: 1.8; }
        .rh-meta strong { color: #fff; }

        .rh-period-band {
            margin-top: 16px; padding: 10px 14px;
            background: rgba(212,69,26,0.15);
            border: 1px solid rgba(212,69,26,0.3);
            border-radius: 6px;
            display: flex; gap: 28px; align-items: center; flex-wrap: wrap;
        }
        .rh-period-item { display: flex; flex-direction: column; gap: 1px; }
        .rh-period-lbl { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #FCA571; }
        .rh-period-val { font-size: 13px; font-weight: 700; color: #fff; }

        /* ══ SECTION TITLE ══ */
        .stitle {
            font-size: 10px; font-weight: 800; letter-spacing: 1.5px;
            text-transform: uppercase; color: #fff;
            padding: 9px 32px 9px 14px;
            background: #374151;
            display: flex; align-items: center; gap: 10px;
            border-top: 1px solid #4B5563;
            border-bottom: 1px solid #4B5563;
        }
        .stitle-num {
            display: inline-flex; align-items: center; justify-content: center;
            width: 20px; height: 20px; border-radius: 50%;
            background: #D4451A; color: #fff;
            font-size: 10px; font-weight: 800; flex-shrink: 0;
        }
        .stitle-sub {
            margin-left: auto; font-size: 10px; font-weight: 500;
            color: rgba(255,255,255,0.4); text-transform: none; letter-spacing: 0;
        }

        /* ══ SECTION BODY ══ */
        .sbody { padding: 20px 32px 22px; }

        /* ══ CALLOUT ══ */
        .callout {
            background: #FFFBEB; border: 1px solid #FCD34D;
            border-radius: 6px; padding: 12px 16px;
            font-size: 13px; color: #78350F; line-height: 1.6;
            margin-bottom: 16px;
        }
        .callout strong { color: #92400E; }

        /* ══ KPI GRID ══ */
        .kpis { display: grid; gap: 10px; margin-bottom: 14px; }
        .kpis-2 { grid-template-columns: repeat(2, 1fr); }
        .kpis-3 { grid-template-columns: repeat(3, 1fr); }
        .kpis-4 { grid-template-columns: repeat(4, 1fr); }
        .kpis-5 { grid-template-columns: repeat(5, 1fr); }

        .kpi {
            background: #F9FAFB; border: 1.5px solid #E5E7EB;
            border-radius: 8px; padding: 14px 12px; text-align: center;
            min-width: 0;
        }
        .kpi-lbl  { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6B7280; margin-bottom: 6px; line-height: 1.3; }
        .kpi-val  { font-size: 26px; font-weight: 800; color: #111827; line-height: 1; word-break: break-all; }
        .kpi-val--lg { font-size: 18px; }
        .kpi-val--xl { font-size: 14px; }
        .kpi-hint { font-size: 11px; color: #9CA3AF; margin-top: 4px; }

        /* ══ COLOUR UTILITIES ══ */
        .c-green  { color: #166534; }
        .c-red    { color: #991B1B; }
        .c-orange { color: #92400E; }
        .c-blue   { color: #1E3A8A; }
        .c-amber  { color: #92400E; }
        .c-muted  { color: #6B7280; }

        /* ══ NO DATA ══ */
        .nodata {
            padding: 28px; text-align: center;
            background: #F9FAFB; border: 1.5px dashed #D1D5DB;
            border-radius: 6px; color: #6B7280; font-size: 13px;
        }
        .nodata-icon { font-size: 1.8em; margin-bottom: 6px; }

        /* ══ TABLES ══ */
        .twrap { border-radius: 6px; overflow: hidden; border: 1.5px solid #E5E7EB; }
        .twrap + .twrap { margin-top: 12px; }
        table  { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead tr { background: #1F2937; }
        th {
            color: #E5E7EB; padding: 10px 12px;
            text-align: left; font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;
        }
        th.r, td.r { text-align: right; }
        th.c, td.c { text-align: center; }
        td { padding: 10px 12px; border-bottom: 1px solid #F3F4F6; color: #374151; vertical-align: middle; font-size: 13px; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:nth-child(even) td { background: #F9FAFB; }
        tbody tr:hover td { background: #FEF3C7 !important; }

        .tn  { font-weight: 700; color: #111827; }
        .tm  { color: #6B7280; font-size: 12px; }
        .tg  { font-weight: 700; color: #166534; }
        .tr  { font-weight: 700; color: #991B1B; }
        .to  { font-weight: 700; color: #92400E; }
        .tb  { font-weight: 700; color: #111827; }

        /* ══ TFOOT ══ */
        tfoot tr td {
            background: #1F2937 !important; color: #F9FAFB !important;
            font-weight: 700 !important; font-size: 12px !important;
            padding: 10px 12px !important; border-bottom: none !important;
        }
        tfoot .tot-label { color: rgba(255,255,255,0.4) !important; font-size: 9px !important; text-transform: uppercase; letter-spacing: 0.8px; }
        tfoot .tot-hi    { color: #6EE7B7 !important; font-size: 13px !important; font-weight: 800 !important; }
        tfoot .tot-amber { color: #FCD34D !important; }

        /* ══ BADGES ══ */
        .badge {
            display: inline-flex; align-items: center; gap: 3px;
            padding: 2px 8px; border-radius: 20px;
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }
        .b-cash  { background: #DCFCE7; color: #166534; }
        .b-qr    { background: #DBEAFE; color: #1E40AF; }
        .b-low   { background: #FEF9C3; color: #854D0E; }
        .b-ok    { background: #DCFCE7; color: #166534; }
        .b-zero  { background: #FEE2E2; color: #991B1B; }

        /* ══ TWO-COL LAYOUT ══ */
        .twocol { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .col-title {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.5px; color: #6B7280;
            padding-bottom: 8px; margin-bottom: 10px;
            border-bottom: 1.5px solid #E5E7EB;
        }
        .col-title strong { color: #166534; margin-left: 6px; }

        /* ══ PACKAGE BARS ══ */
        .pkg-box { background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 6px; padding: 14px 16px; }
        .pkg-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .pkg-row:last-child { margin-bottom: 0; }
        .pkg-lbl { font-size: 13px; font-weight: 600; width: 64px; color: #374151; flex-shrink: 0; }
        .pkg-bar-bg { flex: 1; height: 14px; background: #E5E7EB; border-radius: 6px; overflow: hidden; }
        .pkg-bar { height: 100%; border-radius: 6px; }
        .pb-basic   { background: #D4451A; }
        .pb-premium { background: #D97706; }
        .pb-deluxe  { background: #1D4ED8; }
        .pkg-ct { font-size: 12px; font-weight: 700; color: #374151; width: 54px; text-align: right; flex-shrink: 0; }

        /* ══ PEAK HOURS ══ */
        .peak-highlight {
            display: inline-flex; align-items: center; gap: 8px;
            background: #FEF3C7; color: #78350F;
            font-size: 13px; font-weight: 700;
            padding: 10px 16px; border-radius: 6px;
            border: 1px solid #FCD34D; margin-bottom: 14px;
        }

        /* ══ EXTRAS ROWS ══ */
        .ext-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #F3F4F6; }
        .ext-row:last-child { border-bottom: none; }
        .ext-rank  { width: 18px; font-size: 10px; font-weight: 700; color: #9CA3AF; flex-shrink: 0; text-align: center; }
        .ext-name  { flex: 1; font-size: 13px; font-weight: 600; color: #111827; }
        .ext-bwrap { width: 100px; height: 6px; background: #E5E7EB; border-radius: 4px; overflow: hidden; flex-shrink: 0; }
        .ext-bar   { height: 100%; border-radius: 4px; background: #D4451A; }
        .ext-qty   { font-size: 11px; color: #9CA3AF; width: 34px; text-align: right; flex-shrink: 0; }
        .ext-rev   { font-size: 13px; font-weight: 700; color: #111827; width: 76px; text-align: right; flex-shrink: 0; }
        .ext-box   { background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 6px; padding: 12px 14px; min-height: 60px; }

        /* ══ DISCOUNT CARDS ══ */
        .disc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
        .disc-card { background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 6px; padding: 14px 12px; }
        .disc-emoji  { font-size: 22px; margin-bottom: 6px; }
        .disc-type   { font-size: 10px; font-weight: 700; color: #6B7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.3px; }
        .disc-count  { font-size: 28px; font-weight: 800; line-height: 1; color: #111827; }
        .disc-amount { font-size: 12px; color: #6B7280; margin-top: 5px; font-weight: 600; }
        .disc-pct    { font-size: 10px; color: #9CA3AF; margin-top: 2px; }

        /* ══ P&L SECTION ══ */
        .pnl-verdict {
            display: flex; align-items: center; gap: 16px;
            padding: 16px 20px; border-radius: 6px;
            margin-bottom: 14px; border: 1.5px solid;
        }
        .pnl-verdict-gain { background: #F0FDF4; border-color: #86EFAC; }
        .pnl-verdict-loss { background: #FEF2F2; border-color: #FCA5A5; }
        .pnl-verdict-icon { font-size: 28px; flex-shrink: 0; }
        .pnl-verdict-body { flex: 1; }
        .pnl-verdict-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; color: #374151; }
        .pnl-verdict-amount { font-size: 32px; font-weight: 800; line-height: 1; margin-bottom: 4px; }
        .pnl-verdict-gain .pnl-verdict-amount { color: #166534; }
        .pnl-verdict-loss .pnl-verdict-amount { color: #991B1B; }
        .pnl-verdict-sub { font-size: 12px; color: #6B7280; }
        .pnl-verdict-sub strong { color: #374151; }
        .pnl-verdict-margin { flex-shrink: 0; }
        .pnl-verdict-margin-ring {
            width: 64px; height: 64px; border-radius: 50%;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            border: 3px solid; font-weight: 800;
        }
        .pnl-verdict-margin-ring span { font-size: 16px; line-height: 1; }
        .pnl-verdict-margin-ring small { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.7; }
        .ring-good { border-color: #16A34A; color: #166534; background: #DCFCE7; }
        .ring-ok   { border-color: #D97706; color: #92400E; background: #FEF9C3; }
        .ring-bad  { border-color: #DC2626; color: #991B1B; background: #FEE2E2; }

        .pnl-banner {
            display: flex; align-items: stretch; gap: 0;
            background: #F9FAFB; border: 1px solid #E5E7EB;
            border-radius: 8px; overflow: hidden; margin-bottom: 14px;
        }
        .pnl-col { flex: 1; padding: 18px 16px; text-align: center; border-right: 1px solid #E5E7EB; }
        .pnl-col:last-child { border-right: none; }
        .pnl-col-profit-pos { background: #F0FDF4; border-left: 1px solid #BBF7D0; }
        .pnl-col-profit-neg { background: #FEF2F2; border-left: 1px solid #FECACA; }
        .pnl-col-icon  { font-size: 20px; margin-bottom: 5px; }
        .pnl-col-lbl   { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6B7280; margin-bottom: 6px; }
        .pnl-col-val   { font-size: 20px; font-weight: 800; line-height: 1; margin-bottom: 5px; word-break: break-all; }
        .pnl-col-hint  { font-size: 11px; color: #6B7280; }
        .pnl-minus, .pnl-equals { font-size: 24px; font-weight: 700; color: #9CA3AF; padding: 0 6px; flex-shrink: 0; align-self: center; }
        .pnl-green  { color: #166534; }
        .pnl-red    { color: #991B1B; }
        .pnl-orange { color: #92400E; }
        .pnl-blue   { color: #1E3A8A; }
        .pnl-margin-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .pnl-badge-good { background: #DCFCE7; color: #166534; }
        .pnl-badge-ok   { background: #FEF9C3; color: #854D0E; }
        .pnl-badge-warn { background: #FEE2E2; color: #991B1B; }

        .pnl-breakdown { border: 1px solid #E5E7EB; border-radius: 8px; overflow: hidden; }
        .pnl-row { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-bottom: 1px solid #F3F4F6; background: #fff; }
        .pnl-row:last-child { border-bottom: none; }
        .pnl-row:nth-child(even) { background: #F9FAFB; }
        .pnl-row-restock { background: #EFF6FF !important; border-bottom-color: #BFDBFE !important; }
        .pnl-row-total { background: #1F2937 !important; }
        .pnl-row-total .pnl-row-title { color: #F9FAFB !important; }
        .pnl-row-total .pnl-row-desc  { color: rgba(255,255,255,0.5) !important; }
        .pnl-row-icon  { font-size: 16px; flex-shrink: 0; width: 24px; text-align: center; }
        .pnl-row-label { flex: 1; }
        .pnl-row-title { font-size: 13px; font-weight: 600; color: #111827; }
        .pnl-row-desc  { font-size: 11px; color: #6B7280; margin-top: 2px; line-height: 1.4; }
        .pnl-row-val   { font-size: 14px; font-weight: 700; white-space: nowrap; flex-shrink: 0; min-width: 110px; text-align: right; }

        /* ══ GRAND TOTAL PANEL ══ */
        .grand-panel { background: #1F2937; border-radius: 8px; overflow: hidden; }
        .grand-panel-head {
            background: #374151; padding: 8px 18px;
            font-size: 9px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 1.5px; color: rgba(255,255,255,0.5);
        }
        .grand-panel-body { display: grid; grid-template-columns: repeat(4, 1fr); }
        .grand-cell { padding: 14px 16px; border-right: 1px solid rgba(255,255,255,0.06); }
        .grand-cell:last-child { border-right: none; background: rgba(212,69,26,0.15); }
        .grand-cell-lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: rgba(255,255,255,0.4); margin-bottom: 4px; }
        .grand-cell-val { font-size: 22px; font-weight: 800; color: #fff; line-height: 1; word-break: break-all; }
        .grand-cell:last-child .grand-cell-lbl { color: #FCA571; }
        .grand-cell:last-child .grand-cell-val { color: #FCD34D; }
        .grand-cell-sub { font-size: 10px; color: rgba(255,255,255,0.3); margin-top: 3px; }

        /* ══ SIGNATURE BLOCK ══ */
        .sig-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .sig-line { margin-top: 32px; border-top: 1px solid #D1D5DB; padding-top: 6px; }
        .sig-name { font-size: 12px; font-weight: 600; color: #111827; }
        .sig-role { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #6B7280; margin-top: 2px; }

        /* ══ FOOTER ══ */
        .rfooter {
            padding: 12px 32px;
            background: #F9FAFB; border-top: 1px solid #E5E7EB;
            display: flex; justify-content: space-between; align-items: center;
            font-size: 12px; color: #374151;
        }

        /* ══ MISC ══ */
        .section-divider { height: 8px; background: #F3F4F6; border-top: 1px solid #E5E7EB; border-bottom: 1px solid #E5E7EB; }
        .spacer { height: 20px; }
        .pgfooter { display: none; }
        .screen-only { display: block; }
        .print-only  { display: none; }
        .cat-row td {
            background: #F3F4F6 !important; font-weight: 700 !important;
            font-size: 10px !important; text-transform: uppercase !important;
            letter-spacing: 0.8px !important; color: #374151 !important;
            padding: 6px 12px !important;
        }

        /* ══════════════════════════════════════════════
           PRINT STYLES — optimised for A4, black ink
           Works on: inkjet, laser, B&W, thermal A4
        ═══════════════════════════════════════════════ */
        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm 14mm 20mm 14mm;
            }

            /* Force black ink, show backgrounds */
            *, *::before, *::after {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            html, body {
                background: #fff !important;
                font-family: Arial, Helvetica, sans-serif !important;
                font-size: 9pt !important;
                color: #000 !important;
                line-height: 1.35 !important;
            }

            /* Hide screen-only elements */
            .toolbar  { display: none !important; }
            .screen-only { display: none !important; }
            .print-only  { display: block !important; }

            /* Wrapper — full width, no shadow */
            .wrap {
                margin: 0 !important;
                max-width: 100% !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                border: none !important;
                overflow: visible !important;
            }

            /* ── REPORT HEADER ── */
            .rh {
                background: #1F2937 !important;
                padding: 10pt 14pt !important;
                break-inside: avoid !important;
                break-after: avoid !important;
            }
            .rh-name  { font-size: 14pt !important; }
            .rh-sub   { font-size: 7pt !important; }
            .rh-meta  { font-size: 7.5pt !important; line-height: 1.7 !important; }
            .rh-logo  { height: 32pt !important; width: 32pt !important; }
            .rh-period-band {
                padding: 6pt 10pt !important;
                margin-top: 8pt !important;
                gap: 18pt !important;
            }
            .rh-period-lbl { font-size: 6.5pt !important; }
            .rh-period-val { font-size: 9.5pt !important; }

            /* ── SECTION TITLES ── */
            .stitle {
                background: #374151 !important;
                padding: 5pt 14pt 5pt 10pt !important;
                font-size: 7pt !important;
                break-after: avoid !important;
                break-before: auto !important;
            }
            .stitle-num { width: 13pt !important; height: 13pt !important; font-size: 7pt !important; background: #D4451A !important; }

            /* ── SECTION BODY ── */
            .sbody { padding: 8pt 12pt 10pt !important; }
            .section-divider { height: 0 !important; border-top: 0.5pt solid #E5E7EB !important; }
            .spacer { height: 3pt !important; }

            /* ── KPIs ── */
            .kpis { gap: 4pt !important; margin-bottom: 8pt !important; break-inside: avoid !important; }
            .kpis-4 { grid-template-columns: repeat(4, 1fr) !important; }
            .kpis-3 { grid-template-columns: repeat(3, 1fr) !important; }
            .kpis-2 { grid-template-columns: repeat(2, 1fr) !important; }
            .kpi {
                padding: 5pt 4pt !important;
                border-radius: 0 !important;
                border: 0.5pt solid #D1D5DB !important;
                background: #F9FAFB !important;
                text-align: center !important;
                break-inside: avoid !important;
            }
            .kpi-lbl  { font-size: 6pt !important; margin-bottom: 2pt !important; color: #6B7280 !important; letter-spacing: 0 !important; }
            .kpi-val  { font-size: 13pt !important; font-weight: bold !important; color: #000 !important; }
            .kpi-val--lg { font-size: 10pt !important; }
            .kpi-val--xl { font-size: 8pt !important; }
            .kpi-hint { font-size: 6pt !important; color: #6B7280 !important; }
            .c-green  { color: #166534 !important; }
            .c-red    { color: #991B1B !important; }
            .c-orange { color: #92400E !important; }
            .c-blue   { color: #1E3A8A !important; }
            .c-amber  { color: #92400E !important; }

            /* ── TABLES ── */
            .twrap {
                border: 0.5pt solid #D1D5DB !important;
                border-radius: 0 !important;
                margin-bottom: 8pt !important;
                overflow: visible !important;
                page-break-inside: auto !important;
            }
            table   { font-size: 8pt !important; border-collapse: collapse !important; }
            thead   { display: table-header-group !important; }
            tfoot   { display: table-footer-group !important; }
            thead tr { background: #1F2937 !important; break-inside: avoid !important; }
            th {
                padding: 4pt 6pt !important;
                font-size: 6.5pt !important;
                background: #1F2937 !important;
                color: #E5E7EB !important;
                font-weight: bold !important;
                white-space: nowrap !important;
            }
            td {
                padding: 3.5pt 6pt !important;
                border-bottom: 0.5pt solid #E5E7EB !important;
                font-size: 8pt !important;
                color: #000 !important;
            }
            tbody tr:nth-child(even) td { background: #F9FAFB !important; }
            tbody tr:hover td { background: transparent !important; }
            tfoot tr td {
                padding: 4pt 6pt !important;
                font-size: 7.5pt !important;
                font-weight: bold !important;
                background: #1F2937 !important;
                color: #F9FAFB !important;
                border-bottom: none !important;
            }
            tfoot .tot-label { color: rgba(255,255,255,0.4) !important; font-size: 6pt !important; }
            tfoot .tot-hi    { color: #6EE7B7 !important; font-size: 9pt !important; font-weight: bold !important; }
            tfoot .tot-amber { color: #FCD34D !important; }
            tr { break-inside: avoid !important; }
            .tn { font-size: 8pt !important; font-weight: bold !important; }
            .tm { font-size: 7.5pt !important; color: #4B5563 !important; }
            .tg { color: #166534 !important; font-weight: bold !important; }
            .tr { color: #991B1B !important; font-weight: bold !important; }
            .to { color: #92400E !important; font-weight: bold !important; }
            .tb { font-weight: bold !important; color: #000 !important; }
            .cat-row td {
                background: #E5E7EB !important;
                font-size: 6.5pt !important;
                padding: 3pt 6pt !important;
                color: #374151 !important;
            }

            /* ── TWOCOL ── */
            .twocol {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 8pt !important;
                break-inside: avoid !important;
            }
            .col-title { font-size: 7pt !important; padding-bottom: 3pt !important; margin-bottom: 4pt !important; border-bottom: 0.5pt solid #D1D5DB !important; }

            /* ── PACKAGE BARS ── */
            .pkg-box { padding: 7pt 8pt !important; border: 0.5pt solid #D1D5DB !important; border-radius: 0 !important; background: #F9FAFB !important; }
            .pkg-row { margin-bottom: 5pt !important; }
            .pkg-lbl { font-size: 8pt !important; width: 46pt !important; }
            .pkg-bar-bg { height: 8pt !important; border-radius: 2pt !important; }
            .pkg-ct  { font-size: 8pt !important; width: 36pt !important; }

            /* ── PEAK HOURS ── */
            .peak-highlight { font-size: 8pt !important; padding: 4pt 8pt !important; border-radius: 0 !important; margin-bottom: 6pt !important; background: #FEF9C3 !important; border-color: #FCD34D !important; }

            /* ── EXTRAS ── */
            .ext-box  { padding: 5pt 7pt !important; border: 0.5pt solid #D1D5DB !important; border-radius: 0 !important; background: #F9FAFB !important; }
            .ext-row  { padding: 3pt 0 !important; }
            .ext-name { font-size: 8pt !important; }
            .ext-rev  { font-size: 8pt !important; width: 48pt !important; }
            .ext-qty  { font-size: 7pt !important; }
            .ext-bwrap{ width: 48pt !important; height: 5pt !important; }

            /* ── DISCOUNT CARDS ── */
            .disc-grid { display: flex !important; gap: 5pt !important; flex-wrap: nowrap !important; break-inside: avoid !important; }
            .disc-card { flex: 1 !important; padding: 5pt 6pt !important; border: 0.5pt solid #D1D5DB !important; border-radius: 0 !important; background: #F9FAFB !important; break-inside: avoid !important; }
            .disc-emoji  { font-size: 11pt !important; margin-bottom: 2pt !important; }
            .disc-type   { font-size: 6pt !important; color: #4B5563 !important; }
            .disc-count  { font-size: 14pt !important; font-weight: bold !important; }
            .disc-amount { font-size: 7pt !important; color: #374151 !important; }
            .disc-pct    { font-size: 6pt !important; }

            /* ── CALLOUT ── */
            .callout { font-size: 7pt !important; padding: 4pt 7pt !important; margin-bottom: 6pt !important; background: #FFFBEB !important; border: 0.5pt solid #FCD34D !important; border-radius: 0 !important; }

            /* ── P&L ── */
            .pnl-verdict { padding: 8pt 10pt !important; margin-bottom: 8pt !important; break-inside: avoid !important; border-radius: 0 !important; }
            .pnl-verdict-amount { font-size: 18pt !important; }
            .pnl-verdict-label  { font-size: 7pt !important; }
            .pnl-verdict-sub    { font-size: 7.5pt !important; }
            .pnl-verdict-margin-ring { width: 36pt !important; height: 36pt !important; border-width: 2pt !important; }
            .pnl-verdict-margin-ring span { font-size: 10pt !important; }

            .pnl-banner { border-radius: 0 !important; margin-bottom: 6pt !important; break-inside: avoid !important; }
            .pnl-col { padding: 8pt 10pt !important; }
            .pnl-col-val { font-size: 14pt !important; }
            .pnl-col-lbl { font-size: 6.5pt !important; }
            .pnl-minus, .pnl-equals { font-size: 16pt !important; padding: 0 3pt !important; }

            .pnl-breakdown { border-radius: 0 !important; border: 0.5pt solid #D1D5DB !important; }
            .pnl-row { padding: 5pt 8pt !important; gap: 8pt !important; }
            .pnl-row-title { font-size: 8pt !important; }
            .pnl-row-desc  { font-size: 6.5pt !important; }
            .pnl-row-val   { font-size: 9pt !important; min-width: 70pt !important; }

            /* ── GRAND PANEL ── */
            .grand-panel { border-radius: 0 !important; break-inside: avoid !important; }
            .grand-panel-head { padding: 4pt 8pt !important; font-size: 6.5pt !important; background: #374151 !important; }
            .grand-panel-body { grid-template-columns: repeat(4, 1fr) !important; }
            .grand-cell { padding: 6pt 8pt !important; }
            .grand-cell-lbl { font-size: 6pt !important; }
            .grand-cell-val { font-size: 12pt !important; }
            .grand-cell-sub { font-size: 6.5pt !important; }

            /* ── SIGNATURES ── */
            .sig-grid { gap: 16pt !important; break-inside: avoid !important; }
            .sig-line  { margin-top: 22pt !important; border-top: 0.75pt solid #9CA3AF !important; }
            .sig-name  { font-size: 8pt !important; }
            .sig-role  { font-size: 6.5pt !important; }

            /* ── FOOTER ── */
            .rfooter { padding: 5pt 12pt !important; font-size: 7pt !important; border-top: 0.5pt solid #D1D5DB !important; background: #F9FAFB !important; break-inside: avoid !important; }

            /* ── BADGES ── */
            .badge { padding: 1pt 4pt !important; font-size: 6.5pt !important; font-weight: bold !important; border-radius: 2pt !important; }

            /* ── PAGE FOOTER (repeats on every page) ── */
            .pgfooter {
                display: flex !important;
                position: fixed !important;
                bottom: 0 !important; left: 0 !important; right: 0 !important;
                height: 12mm !important;
                border-top: 0.5pt solid #9CA3AF !important;
                background: #fff !important;
                padding: 0 14mm !important;
                align-items: center !important;
                justify-content: space-between !important;
                font-size: 7pt !important;
                color: #4B5563 !important;
                font-family: Arial, Helvetica, sans-serif !important;
            }

            /* ── NODATA ── */
            .nodata { border: 0.5pt dashed #D1D5DB !important; border-radius: 0 !important; padding: 14pt !important; background: #F9FAFB !important; }
        }
    </style>
</head>
<body>

{{-- Toolbar (screen only) --}}
<div class="toolbar screen-only">
    <span class="toolbar-title">📄 &nbsp;<strong>SamgyHann 199</strong> &nbsp;·&nbsp; Sales Report &nbsp;·&nbsp; {{ $periodLabel }}</span>
    <button class="btn-print" onclick="window.print()">🖨️ &nbsp;Print / Save as PDF</button>
</div>

<div class="wrap">

    {{-- ══ REPORT HEADER ══ --}}
    <div class="rh">
        <div class="rh-top">
            <div class="rh-brand">
                <img src="{{ asset('samgyhann-logo.png') }}" alt="SamgyHann Logo" class="rh-logo">
                <div>
                    <div class="rh-name">SamgyHann 199</div>
                    <div class="rh-sub">Unlimited Samgyeopsal &nbsp;·&nbsp; Olongapo City, Zambales</div>
                </div>
            </div>
            <div class="rh-meta">
                <div><strong>Report Generated:</strong> {{ $generatedAt }}</div>
                <div><strong>Prepared By:</strong> {{ $generatedBy }}</div>
                <div><strong>System:</strong> SamgyHann POS</div>
            </div>
        </div>
        <div class="rh-period-band">
            <div class="rh-period-item">
                <span class="rh-period-lbl">📋 Report Type</span>
                <span class="rh-period-val">Sales Report</span>
            </div>
            <div class="rh-period-item">
                <span class="rh-period-lbl">📅 Period Covered</span>
                <span class="rh-period-val">{{ $periodLabel }}</span>
            </div>
            <div class="rh-period-item">
                <span class="rh-period-lbl">🗓️ From</span>
                <span class="rh-period-val">{{ $reportStartDate }}</span>
            </div>
            <div class="rh-period-item">
                <span class="rh-period-lbl">🗓️ To</span>
                <span class="rh-period-val">{{ $reportEndDate }}</span>
            </div>
            <div class="rh-period-item" style="margin-left:auto;">
                <span class="rh-period-lbl">🖨️ Printed On</span>
                <span class="rh-period-val" style="font-size:12px;">{{ $printedOn }}</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════
         SECTION 1 — SUMMARY
    ══════════════════════════════════ --}}
    <div class="stitle"><span class="stitle-num">1</span> Summary Overview</div>

    @if((int)$summary->total_orders === 0)
        <div class="sbody">
            <div class="nodata">
                <div class="nodata-icon">📭</div>
                No completed orders found for this period.
            </div>
        </div>
    @else

    <div class="sbody">
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

        {{-- What does this section tell you? (screen callout) --}}
        <div class="kpis kpis-4">
            <div class="kpi" style="border-color:#bfdbfe;">
                <div class="kpi-lbl">Total Orders</div>
                <div class="kpi-val c-blue">{{ number_format($summary->total_orders) }}</div>
                <div class="kpi-hint">tables served</div>
            </div>
            <div class="kpi" style="border-color:#bbf7d0;">
                <div class="kpi-lbl">Total Revenue</div>
                <div class="kpi-val c-green {{ kpiSizeClass($revStr) }}">{{ $revStr }}</div>
                <div class="kpi-hint">money collected</div>
            </div>
            <div class="kpi" style="border-color:#fed7aa;">
                <div class="kpi-lbl">Total Guests</div>
                <div class="kpi-val c-orange">{{ number_format($summary->total_guests) }}</div>
                <div class="kpi-hint">people served</div>
            </div>
            <div class="kpi" style="border-color:#fecaca;">
                <div class="kpi-lbl">Total Discounts Given</div>
                <div class="kpi-val c-red {{ kpiSizeClass($discStr) }}">{{ $discStr }}</div>
                <div class="kpi-hint">deducted from sales</div>
            </div>
        </div>
        <div class="kpis kpis-3">
            <div class="kpi">
                <div class="kpi-lbl">Average Order Value</div>
                <div class="kpi-val c-amber {{ kpiSizeClass($avgStr) }}">{{ $avgStr }}</div>
                <div class="kpi-hint">per table / order</div>
            </div>
            <div class="kpi">
                <div class="kpi-lbl">Revenue Per Guest</div>
                <div class="kpi-val {{ kpiSizeClass($rpgStr) }}">{{ $rpgStr }}</div>
                <div class="kpi-hint">per person (per head)</div>
            </div>
            <div class="kpi">
                <div class="kpi-lbl">Average Party Size</div>
                <div class="kpi-val">{{ number_format($summary->avg_party_size, 1) }}</div>
                <div class="kpi-hint">guests per table</div>
            </div>
        </div>
    </div>{{-- /sbody section 1 --}}

    <div class="section-divider"></div>

    {{-- ══════════════════════════════════
         SECTION 1B — PROFIT & LOSS
    ══════════════════════════════════ --}}
    <div class="stitle"><span class="stitle-num" style="background:#166534;">₱</span> Revenue vs Cost (Profit &amp; Loss)</div>
    <div class="sbody">
        {{-- VERDICT BOX — prominent gain/loss at a glance --}}
        <div class="pnl-verdict {{ $grossProfit >= 0 ? 'pnl-verdict-gain' : 'pnl-verdict-loss' }}">
            <div class="pnl-verdict-icon">{{ $grossProfit >= 0 ? '📈' : '📉' }}</div>
            <div class="pnl-verdict-body">
                <div class="pnl-verdict-label">{{ $grossProfit >= 0 ? '✅ NET GAIN — You are profitable this period' : '❌ NET LOSS — Costs exceeded revenue this period' }}</div>
                <div class="pnl-verdict-amount">{{ $grossProfit >= 0 ? '+' : '−' }}₱{{ number_format(abs($grossProfit), 2) }}</div>
                <div class="pnl-verdict-sub">
                    Gross margin: <strong>{{ $grossMarginPct }}%</strong>
                    &nbsp;·&nbsp;
                    Revenue: <strong>₱{{ number_format($summary->total_revenue, 2) }}</strong>
                    &nbsp;·&nbsp;
                    Total cost: <strong>₱{{ number_format($totalCogs, 2) }}</strong>
                </div>
            </div>
            <div class="pnl-verdict-margin">
                <div class="pnl-verdict-margin-ring {{ $grossMarginPct >= 50 ? 'ring-good' : ($grossMarginPct >= 25 ? 'ring-ok' : 'ring-bad') }}">
                    <span>{{ $grossMarginPct }}%</span>
                    <small>margin</small>
                </div>
            </div>
        </div>

        <div class="callout" style="margin-bottom:14px;">
            📈 <strong>What is Profit &amp; Loss?</strong>
            It compares the money you <em>earned</em> (Revenue) against the money you <em>spent on ingredients and stock</em> (Cost of Goods Sold / COGS).
            What's left after subtracting the cost is your <strong>Gross Profit</strong> — the actual money your business made.
            A <strong>gain</strong> means revenue covered all costs with money to spare. A <strong>loss</strong> means costs were higher than earnings.
            <em>Note: This only counts ingredient/stock costs tracked in the inventory — it does not include rent, utilities, or staff wages.</em>
        </div>

        {{-- Main P&L banner --}}
        <div class="pnl-banner">
            <div class="pnl-col pnl-col-rev">
                <div class="pnl-col-icon">📥</div>
                <div class="pnl-col-lbl">Total Revenue Earned</div>
                <div class="pnl-col-val pnl-green">₱{{ number_format($summary->total_revenue, 2) }}</div>
                <div class="pnl-col-hint">Money collected from customers</div>
            </div>
            <div class="pnl-minus">−</div>
            <div class="pnl-col pnl-col-cost">
                <div class="pnl-col-icon">📦</div>
                <div class="pnl-col-lbl">Cost of Goods Sold (COGS)</div>
                <div class="pnl-col-val pnl-red">₱{{ number_format($totalCogs, 2) }}</div>
                <div class="pnl-col-hint">Estimated ingredient/stock cost used</div>
            </div>
            <div class="pnl-equals">=</div>
            <div class="pnl-col pnl-col-profit {{ $grossProfit >= 0 ? 'pnl-col-profit-pos' : 'pnl-col-profit-neg' }}">
                <div class="pnl-col-icon">{{ $grossProfit >= 0 ? '🏆' : '⚠️' }}</div>
                <div class="pnl-col-lbl">Gross Profit</div>
                <div class="pnl-col-val {{ $grossProfit >= 0 ? 'pnl-green' : 'pnl-red' }}">
                    {{ $grossProfit >= 0 ? '' : '−' }}₱{{ number_format(abs($grossProfit), 2) }}
                </div>
                <div class="pnl-col-hint">
                    <span class="pnl-margin-badge {{ $grossMarginPct >= 50 ? 'pnl-badge-good' : ($grossMarginPct >= 25 ? 'pnl-badge-ok' : 'pnl-badge-warn') }}">
                        {{ $grossMarginPct }}% margin
                    </span>
                </div>
            </div>
        </div>

        {{-- Breakdown rows --}}
        <div class="pnl-breakdown">
            <div class="pnl-row">
                <div class="pnl-row-icon">🧾</div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Revenue from Packages & Add-ons</div>
                    <div class="pnl-row-desc">Money collected from dine-in customers (after discounts applied)</div>
                </div>
                <div class="pnl-row-val pnl-green">₱{{ number_format($summary->total_revenue, 2) }}</div>
            </div>
            @if($totalExtrasRevenue > 0)
            <div class="pnl-row">
                <div class="pnl-row-icon">🥤</div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Revenue from Extras (Drinks, etc.)</div>
                    <div class="pnl-row-desc">Already included in revenue above — shown separately for clarity</div>
                </div>
                <div class="pnl-row-val" style="color:#1D4ED8;">₱{{ number_format($totalExtrasRevenue, 2) }}</div>
            </div>
            @if($totalExtrasCost > 0)
            <div class="pnl-row">
                <div class="pnl-row-icon">🏷️</div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Cost of Extras Sold</div>
                    <div class="pnl-row-desc">Included in COGS below — extras gross profit: ₱{{ number_format($totalExtrasProfit, 2) }} ({{ $extrasMarginPct }}% margin)</div>
                </div>
                <div class="pnl-row-val pnl-orange">− ₱{{ number_format($totalExtrasCost, 2) }}</div>
            </div>
            @endif
            @endif
            <div class="pnl-row">
                <div class="pnl-row-icon">🥩</div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Cost of Stock Used for Sales</div>
                    <div class="pnl-row-desc">Estimated ingredient cost auto-deducted when orders were processed</div>
                </div>
                <div class="pnl-row-val pnl-red">− ₱{{ number_format($salesCogs, 2) }}</div>
            </div>
            @if($manualCogs > 0)
            <div class="pnl-row">
                <div class="pnl-row-icon">📋</div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Manual Stock Removals</div>
                    <div class="pnl-row-desc">Stock manually removed (waste, spoilage, corrections, etc.)</div>
                </div>
                <div class="pnl-row-val pnl-orange">− ₱{{ number_format($manualCogs, 2) }}</div>
            </div>
            @endif
            <div class="pnl-row">
                <div class="pnl-row-icon">📉</div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Total Discounts Given</div>
                    <div class="pnl-row-desc">SC, PWD, and other discounts deducted from full price (already reflected in revenue above)</div>
                </div>
                <div class="pnl-row-val pnl-orange">− ₱{{ number_format($summary->total_discounts, 2) }}</div>
            </div>
            @if($restockCost > 0)
            <div class="pnl-row pnl-row-restock">
                <div class="pnl-row-icon">🚚</div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Stock Restocking Spend <span style="font-size:10px;font-weight:500;color:#6B7280;">(informational)</span></div>
                    <div class="pnl-row-desc">Estimated cost of stock deliveries received this period — not deducted from profit above</div>
                </div>
                <div class="pnl-row-val pnl-blue">₱{{ number_format($restockCost, 2) }}</div>
            </div>
            @endif
            <div class="pnl-row pnl-row-total">
                <div class="pnl-row-icon">{{ $grossProfit >= 0 ? '✅' : '❌' }}</div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Estimated Gross Profit</div>
                    <div class="pnl-row-desc">Revenue minus cost of all goods used — <strong>your margin is {{ $grossMarginPct }}%</strong></div>
                </div>
                <div class="pnl-row-val {{ $grossProfit >= 0 ? 'pnl-green' : 'pnl-red' }}" style="font-size:18px;font-weight:800;">
                    {{ $grossProfit >= 0 ? '' : '−' }}₱{{ number_format(abs($grossProfit), 2) }}
                </div>
            </div>
        </div>

        {{-- Per-guest breakdown --}}
        <div class="kpis kpis-3" style="margin-top:16px;">
            <div class="kpi" style="border-color:#bbf7d0;">
                <div class="kpi-lbl">Revenue per Guest</div>
                <div class="kpi-val c-green">₱{{ number_format($revenuePerGuest, 2) }}</div>
                <div class="kpi-hint">average earned per person</div>
            </div>
            <div class="kpi" style="border-color:#fecaca;">
                <div class="kpi-lbl">Cost per Guest</div>
                <div class="kpi-val c-red">₱{{ number_format($cogsPerGuest, 2) }}</div>
                <div class="kpi-hint">average ingredient cost per person</div>
            </div>
            <div class="kpi" style="border-color:{{ ($revenuePerGuest - $cogsPerGuest) >= 0 ? '#bbf7d0' : '#fecaca' }};">
                <div class="kpi-lbl">Profit per Guest</div>
                <div class="kpi-val {{ ($revenuePerGuest - $cogsPerGuest) >= 0 ? 'c-green' : 'c-red' }}">
                    ₱{{ number_format($revenuePerGuest - $cogsPerGuest, 2) }}
                </div>
                <div class="kpi-hint">margin earned per person</div>
            </div>
        </div>

        @if($totalCogs == 0)
        <div class="callout" style="margin-top:12px;">
            ⚠️ <strong>Note:</strong> Cost data is ₱0.00 — this usually means product cost prices haven't been set in the Inventory tab,
            or no stock movements were recorded during this period. Set a <strong>Cost Price</strong> per product in Inventory to see accurate profit figures.
        </div>
        @endif
    </div>

    <div class="section-divider"></div>

    {{-- ══════════════════════════════════
         SECTION 2 — BREAKDOWN
    ══════════════════════════════════ --}}
    <div class="stitle"><span class="stitle-num">2</span> Sales Breakdown</div>
    <div class="sbody">
        <div class="twocol">
            <div>
                <div class="col-title">💳 Payment Methods Used</div>
                <div class="twrap">
                    <table>
                        <thead><tr>
                            <th>Payment Type</th><th class="c">No. of Orders</th><th class="r">Total Collected</th><th class="r">% of Sales</th>
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
                            <tr><td colspan="4" class="tm" style="text-align:center;padding:20px;">No payment data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <div class="col-title">📦 Package Popularity <span style="font-size:11px;color:#6B7280;font-weight:500;text-transform:none;">(by number of guests)</span></div>
                @php $maxPkg = max(array_values($packageCounts) ?: [1]); @endphp
                <div class="pkg-box">
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
    </div>

    <div class="section-divider"></div>

    {{-- ══════════════════════════════════
         SECTION 3 — DAILY REVENUE
    ══════════════════════════════════ --}}
    @if($revenueByDay->count() > 0)
    <div class="stitle">
        <span class="stitle-num">3</span> Daily Revenue
        <span class="stitle-sub">{{ $revenueByDay->count() }} days</span>
    </div>
    <div class="sbody">
        <div class="twrap">
            <table>
                <thead><tr>
                    <th>Date</th><th class="c">Orders</th><th class="c">Guests</th>
                    <th class="r">Total Revenue</th><th class="r">Revenue / Order</th><th class="r">Revenue / Guest</th>
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
                    <td class="tot-label">{{ $revenueByDay->count() }}-Day Total</td>
                    <td class="c">{{ number_format($dayTotalOrders) }}</td>
                    <td class="c">{{ number_format($dayTotalGuests) }}</td>
                    <td class="r tot-hi">₱{{ number_format($dayTotalRevenue, 2) }}</td>
                    <td class="r tot-amber">₱{{ $dayTotalOrders > 0 ? number_format($dayTotalRevenue / $dayTotalOrders, 2) : '—' }}</td>
                    <td class="r tot-amber">₱{{ $dayTotalGuests > 0 ? number_format($dayTotalRevenue / $dayTotalGuests, 2) : '—' }}</td>
                </tr></tfoot>
            </table>
        </div>
    </div>
    <div class="section-divider"></div>
    @endif

    {{-- ══════════════════════════════════
         SECTION 4 — PEAK HOURS
    ══════════════════════════════════ --}}
    <div class="stitle"><span class="stitle-num">4</span> Busiest Hours of the Day</div>
    <div class="sbody">
        @if($peakHours->isEmpty())
            <div class="nodata">No hourly data available for this period.</div>
        @else
            @if($busiestHour)
            <div class="peak-highlight">
                🔥 &nbsp;<strong>Busiest Hour:</strong> &nbsp;{{ $busiestHour['label'] }}
                &nbsp;·&nbsp; {{ $busiestHour['orders'] }} orders
                &nbsp;·&nbsp; ₱{{ number_format($busiestHour['revenue'], 2) }} earned
            </div>
            @endif
            <div class="twrap">
                <table>
                    <thead><tr>
                        <th>Hour of Day</th><th class="c">No. of Orders</th><th class="c">Guests</th>
                        <th class="r">Revenue Earned</th><th class="r">Avg per Order</th>
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
    </div>

    <div class="section-divider"></div>

    {{-- ══════════════════════════════════
         SECTION 5 — EXTRAS & ADD-ONS
    ══════════════════════════════════ --}}
    <div class="stitle"><span class="stitle-num">5</span> Extras & Add-ons Sold</div>
    <div class="sbody">

        {{-- Extras profit summary KPIs --}}
        @if($totalExtrasRevenue > 0)
        <div class="kpis kpis-3" style="margin-bottom:16px;">
            <div class="kpi" style="border-color:#bbf7d0;">
                <div class="kpi-lbl">Extras Revenue</div>
                <div class="kpi-val c-green kpi-val--lg">₱{{ number_format($totalExtrasRevenue, 2) }}</div>
                <div class="kpi-hint">total collected from extras</div>
            </div>
            <div class="kpi" style="border-color:#fecaca;">
                <div class="kpi-lbl">Extras Cost (COGS)</div>
                <div class="kpi-val c-red kpi-val--lg">₱{{ number_format($totalExtrasCost, 2) }}</div>
                <div class="kpi-hint">what the store paid for them</div>
            </div>
            <div class="kpi" style="border-color:{{ $totalExtrasProfit >= 0 ? '#bbf7d0' : '#fecaca' }};">
                <div class="kpi-lbl">Extras Gross Profit</div>
                <div class="kpi-val {{ $totalExtrasProfit >= 0 ? 'c-green' : 'c-red' }} kpi-val--lg">
                    {{ $totalExtrasProfit >= 0 ? '' : '−' }}₱{{ number_format(abs($totalExtrasProfit), 2) }}
                </div>
                <div class="kpi-hint">{{ $extrasMarginPct }}% margin on extras</div>
            </div>
        </div>
        @endif

        <div class="twocol">
            <div>
                <div class="col-title">🥩 Extra Items
                    @if($totalExtrasRevenue > 0)<strong>₱{{ number_format($totalExtrasRevenue, 2) }}</strong>@endif
                </div>
                @if(empty($extrasMap))
                    <div class="ext-box"><div style="color:#6B7280;font-size:13px;text-align:center;padding:14px 0;">No extra items sold this period.</div></div>
                @else
                {{-- Full table for extras with cost + profit columns --}}
                <div class="twrap">
                    <table>
                        <thead><tr>
                            <th>#</th>
                            <th>Item</th>
                            <th class="c">Qty</th>
                            <th class="r">Revenue</th>
                            <th class="r">Cost</th>
                            <th class="r">Profit</th>
                            <th class="r">Margin</th>
                        </tr></thead>
                        <tbody>
                            @php $rk = 0; @endphp
                            @foreach($extrasMap as $name => $d)
                            @php
                                $rk++;
                                $margin = $d['revenue'] > 0 ? round(($d['profit'] / $d['revenue']) * 100, 1) : 0;
                                $marginCls = $margin >= 40 ? 'tg' : ($margin >= 15 ? 'to' : 'tr');
                            @endphp
                            <tr>
                                <td class="c tm">#{{ $rk }}</td>
                                <td class="tn">{{ $name }}</td>
                                <td class="c">{{ $d['qty'] }}×</td>
                                <td class="r tb">₱{{ number_format($d['revenue'], 2) }}</td>
                                <td class="r tm">₱{{ number_format($d['cost'], 2) }}</td>
                                <td class="r {{ $d['profit'] >= 0 ? 'tg' : 'tr' }}">
                                    {{ $d['profit'] >= 0 ? '' : '−' }}₱{{ number_format(abs($d['profit']), 2) }}
                                </td>
                                <td class="r {{ $marginCls }}">{{ $margin }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot><tr>
                            <td class="tot-label" colspan="2">Total</td>
                            <td class="c">{{ collect($extrasMap)->sum('qty') }}×</td>
                            <td class="r tot-hi">₱{{ number_format($totalExtrasRevenue, 2) }}</td>
                            <td class="r tot-amber">₱{{ number_format($totalExtrasCost, 2) }}</td>
                            <td class="r tot-hi">₱{{ number_format($totalExtrasProfit, 2) }}</td>
                            <td class="r tot-amber">{{ $extrasMarginPct }}%</td>
                        </tr></tfoot>
                    </table>
                </div>
                @if($totalExtrasCost == 0 && $totalExtrasRevenue > 0)
                <div class="callout" style="margin-top:8px;font-size:12px;">
                    ⚠️ Cost data is ₱0 for all extras — set a <strong>Cost Price</strong> per product in Inventory to see accurate profit figures.
                </div>
                @endif
                @endif
            </div>
            <div>
                <div class="col-title">➕ Add-ons
                    @if($totalAddonsRevenue > 0)<strong>₱{{ number_format($totalAddonsRevenue, 2) }}</strong>@endif
                </div>
                @php $mxA = collect($addonsMap)->max('revenue') ?: 1; $rk = 0; @endphp
                <div class="ext-box">
                    @if(empty($addonsMap))
                        <div style="color:#6B7280;font-size:13px;text-align:center;padding:14px 0;">No add-ons sold this period.</div>
                    @else
                        @foreach($addonsMap as $name => $d)
                        @php $rk++; $bw = round(($d['revenue']/$mxA)*100); @endphp
                        <div class="ext-row">
                            <div class="ext-rank">#{{ $rk }}</div>
                            <div class="ext-name">{{ $name }}</div>
                            <div class="ext-bwrap"><div class="ext-bar" style="width:{{ $bw }}%;background:#4A90D9;"></div></div>
                            <div class="ext-qty">{{ $d['qty'] }}×</div>
                            <div class="ext-rev">₱{{ number_format($d['revenue'], 2) }}</div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="section-divider"></div>

    {{-- ══════════════════════════════════
         SECTION 6 — DISCOUNTS
    ══════════════════════════════════ --}}
    <div class="stitle"><span class="stitle-num">6</span> Discount Summary</div>
    <div class="sbody">
        @if($totalDiscountedGuests === 0)
            <div class="nodata">No discounts were applied during this period.</div>
        @else
            <div class="disc-grid" style="margin-bottom:12px;">
                @foreach($discountTypes as $dt)
                @php $pg = $summary->total_guests > 0 ? round(($dt['count']/$summary->total_guests)*100,1) : 0; @endphp
                <div class="disc-card">
                    <div class="disc-emoji">{{ $dt['emoji'] }}</div>
                    <div class="disc-type">{{ $dt['label'] }}</div>
                    <div class="disc-count" style="color:{{ $dt['color'] }};font-size:28px;font-weight:800;line-height:1;">{{ number_format($dt['count']) }}</div>
                    <div class="disc-amount">₱{{ number_format($dt['amount'], 2) }} total saved</div>
                    <div class="disc-pct">{{ $pg }}% of all guests</div>
                </div>
                @endforeach
                <div class="disc-card" style="background:#FEF9C3;border-color:#FCD34D;">
                    <div class="disc-emoji">🧾</div>
                    <div class="disc-type">Total Discounted</div>
                    <div class="disc-count">{{ number_format($totalDiscountedGuests) }}</div>
                    <div class="disc-amount">₱{{ number_format($summary->total_discounts, 2) }} total</div>
                    <div class="disc-pct">across all discount types</div>
                </div>
            </div>
            <div class="callout" style="border-color:#fbbf24;background:#fffbeb;color:#78350f;">
                ⚠️ <strong>Legal Note:</strong> SC and PWD discounts are mandated by Philippine law (RA 9994 &amp; RA 7277).
                Please keep this report on file for BIR / DTI documentation purposes.
            </div>
        @endif
    </div>

    <div class="section-divider"></div>

    {{-- ══════════════════════════════════
         SECTION 7 — ORDER DETAILS
    ══════════════════════════════════ --}}
    @if($orders->count() > 0)
    <div class="stitle">
        <span class="stitle-num">7</span> Complete Order List
        <span class="stitle-sub">{{ $orders->count() }} orders</span>
    </div>
    <div class="sbody">
        <div class="twrap">
            <table>
                <thead><tr>
                    <th>Receipt #</th><th>Date & Time</th><th class="c">Guests</th>
                    <th>Package(s)</th><th class="c">Payment</th>
                    <th class="r">Before Disc.</th><th class="c">Disc %</th><th class="r">Total Paid</th>
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
                    <td></td>
                    <td class="c">{{ number_format($grandGuests) }}</td>
                    <td></td>
                    <td class="c tot-label">{{ $orders->count() }} orders</td>
                    <td class="r tot-amber">₱{{ number_format($grandSubtotal, 2) }}</td>
                    <td class="c tot-amber">₱{{ number_format($grandDiscounts, 2) }}</td>
                    <td class="r tot-hi">₱{{ number_format($grandTotal, 2) }}</td>
                </tr></tfoot>
            </table>
        </div>
    </div>
    @endif

    @endif {{-- end no orders --}}

    <div class="section-divider"></div>

    {{-- ══════════════════════════════════
         SECTION 9 — INVENTORY SNAPSHOT
    ══════════════════════════════════ --}}
    <div class="stitle"><span class="stitle-num">8</span> Inventory &amp; Stock Status</div>
    <div class="sbody">
        <div class="callout">
            🗃️ <strong>Color guide:</strong> <span style="color:#991b1b;font-weight:700;">Red</span> = out of stock &nbsp;|&nbsp;
            <span style="color:#92400e;font-weight:700;">Orange</span> = low, restock soon &nbsp;|&nbsp;
            <span style="color:#166534;font-weight:700;">Green</span> = OK. &nbsp;
            💰 <strong>Inventory Value</strong> = current stock × cost price (how much capital is in your stockroom).
            &nbsp;The <strong>Opening</strong> and <strong>Closing</strong> columns show stock at the start and end of the selected period.
        </div>
        <div class="kpis kpis-4">
            <div class="kpi">
                <div class="kpi-lbl">Total Items</div>
                <div class="kpi-val c-blue">{{ $inventory->count() }}</div>
                <div class="kpi-hint">products in inventory</div>
            </div>
            <div class="kpi" style="{{ $lowStockCount > 0 ? 'border-color:#fed7aa;' : 'border-color:#bbf7d0;' }}">
                <div class="kpi-lbl">⚠️ Low Stock</div>
                <div class="kpi-val {{ $lowStockCount > 0 ? 'c-orange' : 'c-green' }}">{{ $lowStockCount }}</div>
                <div class="kpi-hint">need to restock soon</div>
            </div>
            <div class="kpi" style="{{ $outOfStockCount > 0 ? 'border-color:#fecaca;' : 'border-color:#bbf7d0;' }}">
                <div class="kpi-lbl">🔴 Out of Stock</div>
                <div class="kpi-val {{ $outOfStockCount > 0 ? 'c-red' : 'c-green' }}">{{ $outOfStockCount }}</div>
                <div class="kpi-hint">empty, order immediately</div>
            </div>
            @php
                $invValStr = '₱' . number_format($totalInventoryValue, 2);
                $invValCls = strlen($invValStr) > 12 ? 'kpi-val--xl' : (strlen($invValStr) > 9 ? 'kpi-val--lg' : '');
            @endphp
            <div class="kpi" style="border-color:#bbf7d0;">
                <div class="kpi-lbl">Estimated Total Value</div>
                <div class="kpi-val c-green {{ $invValCls }}">{{ $invValStr }}</div>
                <div class="kpi-hint">cost × current stock</div>
            </div>
        </div>

        @if($inventory->isEmpty())
            <div class="nodata">No inventory items found.</div>
        @else
            @foreach($inventoryByCategory as $category => $items)
            @php $catValue = $items->sum(fn($i) => (float)$i->stock * (float)$i->cost); @endphp
            <div class="twrap" style="margin-bottom:14px;">
                <table>
                    <thead>
                        <tr>
                            <th colspan="9" style="background:#374151;color:#E5E7EB;font-size:10px;letter-spacing:1px;font-weight:700;">
                                📁 {{ strtoupper($category) }} — {{ $items->count() }} items &nbsp;·&nbsp; Subtotal: ₱{{ number_format($catValue, 2) }}
                            </th>
                        </tr>
                        <tr>
                            <th>Item Name</th>
                            <th class="c">Unit</th>
                            <th class="r">Opening Stock</th>
                            <th class="r">Closing Stock</th>
                            <th class="r">Change</th>
                            <th class="r">Reorder At</th>
                            <th class="r">Cost / Unit</th>
                            <th class="r">Total Value</th>
                            <th class="c">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        @php
                            $oos        = (float)$item->stock <= 0;
                            $low        = !$oos && $item->is_low_stock;
                            $slabel     = $oos ? 'Out of Stock' : ($low ? 'Low Stock' : 'OK');
                            $sbadge     = $oos ? 'b-zero' : ($low ? 'b-low' : 'b-ok');
                            $scls       = $oos ? 'tr' : ($low ? 'to' : 'tg');
                            $ival       = (float)$item->stock * (float)$item->cost;
                            // Single snapshot lookup (not two separate calls)
                            $snap       = collect($stockSnapshot)->firstWhere('name', $item->name);
                            $openingStk = $snap ? (float)$snap['opening_stock'] : (float)$item->stock;
                            $closingStk = $snap ? (float)$snap['closing_stock']  : (float)$item->stock;
                            $hadMov     = $snap ? (bool)$snap['had_movements']   : false;
                            $netChg     = $closingStk - $openingStk;
                            $netPfx     = $netChg > 0 ? '+' : '';
                            $netCls     = $netChg > 0 ? 'tg' : ($netChg < 0 ? 'tr' : 'tm');
                            $closingCls = $closingStk <= 0 ? 'tr' : (($item->reorder_level > 0 && $closingStk <= (float)$item->reorder_level) ? 'to' : 'tg');
                        @endphp
                        <tr>
                            <td class="tn">{{ $item->name }}</td>
                            <td class="c tm">{{ $item->unit }}</td>
                            <td class="r tg">{{ number_format($openingStk, 2) }}</td>
                            <td class="r" style="font-weight:700;color:#1D4ED8;">{{ number_format($closingStk, 2) }}</td>
                            <td class="r {{ $netCls }}">{{ $hadMov ? $netPfx.number_format($netChg,2) : '—' }}</td>
                            <td class="r tm">{{ number_format((float)$item->reorder_level, 2) }}</td>
                            <td class="r tm">₱{{ number_format((float)$item->cost, 2) }}</td>
                            <td class="r tb">₱{{ number_format($ival, 2) }}</td>
                            <td class="c"><span class="badge {{ $sbadge }}">{{ $slabel }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach

            {{-- Grand total --}}
            <div class="grand-panel">
                <div class="grand-panel-head">📦 &nbsp;Grand Total Inventory Value</div>
                <div class="grand-panel-body">
                    <div class="grand-cell">
                        <div class="grand-cell-lbl">Total Items</div>
                        <div class="grand-cell-val">{{ $inventory->count() }}</div>
                    </div>
                    <div class="grand-cell">
                        <div class="grand-cell-lbl">Low Stock Items</div>
                        <div class="grand-cell-val" style="{{ $lowStockCount > 0 ? 'color:#fbbf24;' : 'color:#6ee7b7;' }}">{{ $lowStockCount }}</div>
                    </div>
                    <div class="grand-cell">
                        <div class="grand-cell-lbl">Out of Stock</div>
                        <div class="grand-cell-val" style="{{ $outOfStockCount > 0 ? 'color:#f87171;' : 'color:#6ee7b7;' }}">{{ $outOfStockCount }}</div>
                    </div>
                    <div class="grand-cell">
                        <div class="grand-cell-lbl">🏆 Total Inventory Value</div>
                        <div class="grand-cell-val">₱{{ number_format($totalInventoryValue, 2) }}</div>
                        <div class="grand-cell-sub">estimated at cost price</div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="spacer"></div>



    {{-- ══ DOCUMENT FOOTER ══ --}}
    <div class="rfooter">
        <div>
            <div style="font-weight:700;color:#111827;margin-bottom:3px;">SamgyHann 199 POS &nbsp;·&nbsp; Olongapo City, Zambales</div>
            <div>Period: <strong>{{ $periodLabel }}</strong> &nbsp;({{ $reportStartDate }} – {{ $reportEndDate }})</div>
        </div>
        <div style="text-align:right;">
            <div>Printed: <strong>{{ $printedOn }}</strong></div>
            <div>Prepared by: <strong>{{ $generatedBy }}</strong></div>
        </div>
    </div>

    {{-- ══ SIGNATURE BLOCK ══ --}}
    <div class="sbody" style="padding-top:0;padding-bottom:32px;">
        <div class="sig-grid">
            <div>
                <div class="sig-line">
                    <div class="sig-name">{{ $generatedBy }}</div>
                    <div class="sig-role">Prepared By</div>
                </div>
            </div>
            <div>
                <div class="sig-line">
                    <div class="sig-name">&nbsp;</div>
                    <div class="sig-role">Received By</div>
                </div>
            </div>
            <div>
                <div class="sig-line">
                    <div class="sig-name">&nbsp;</div>
                    <div class="sig-role">Noted By / Approved By</div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /.wrap --}}

{{-- Page footer on every printed page --}}
<div class="pgfooter">
    <span>SamgyHann 199 &nbsp;·&nbsp; Sales Report &nbsp;·&nbsp; {{ $periodLabel }}</span>
    <span>Printed: {{ $printedOn }}</span>
</div>

</body>
</html>
