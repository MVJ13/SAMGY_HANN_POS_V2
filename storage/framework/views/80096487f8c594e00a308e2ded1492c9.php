<!DOCTYPE html>
<html lang="en" data-period="<?php echo e($periodLabel); ?>" data-printed="<?php echo e($printedOn); ?>">
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


<div class="toolbar screen-only">
    <span class="toolbar-title">📄 &nbsp;<strong>SamgyHann 199</strong> &nbsp;·&nbsp; Sales Report &nbsp;·&nbsp; <?php echo e($periodLabel); ?></span>
    <button class="btn-print" onclick="window.print()">🖨️ &nbsp;Print / Save as PDF</button>
</div>

<div class="wrap">

    
    <div class="rh">
        <div class="rh-top">
            <div class="rh-brand">
                <img src="<?php echo e(asset('samgyhann-logo.png')); ?>" alt="SamgyHann Logo" class="rh-logo">
                <div>
                    <div class="rh-name">SamgyHann 199</div>
                    <div class="rh-sub">Unlimited Samgyeopsal &nbsp;·&nbsp; Olongapo City, Zambales</div>
                </div>
            </div>
            <div class="rh-meta">
                <div><strong>Report Generated:</strong> <?php echo e($generatedAt); ?></div>
                <div><strong>Prepared By:</strong> <?php echo e($generatedBy); ?></div>
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
                <span class="rh-period-val"><?php echo e($periodLabel); ?></span>
            </div>
            <div class="rh-period-item">
                <span class="rh-period-lbl">🗓️ From</span>
                <span class="rh-period-val"><?php echo e($reportStartDate); ?></span>
            </div>
            <div class="rh-period-item">
                <span class="rh-period-lbl">🗓️ To</span>
                <span class="rh-period-val"><?php echo e($reportEndDate); ?></span>
            </div>
            <div class="rh-period-item" style="margin-left:auto;">
                <span class="rh-period-lbl">🖨️ Printed On</span>
                <span class="rh-period-val" style="font-size:12px;"><?php echo e($printedOn); ?></span>
            </div>
        </div>
    </div>

    
    <div class="stitle"><span class="stitle-num">1</span> Summary Overview</div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((int)$summary->total_orders === 0): ?>
        <div class="sbody">
            <div class="nodata">
                <div class="nodata-icon">📭</div>
                No completed orders found for this period.
            </div>
        </div>
    <?php else: ?>

    <div class="sbody">
        <?php
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
        ?>

        
        <div class="kpis kpis-4">
            <div class="kpi" style="border-color:#bfdbfe;">
                <div class="kpi-lbl">Total Orders</div>
                <div class="kpi-val c-blue"><?php echo e(number_format($summary->total_orders)); ?></div>
                <div class="kpi-hint">tables served</div>
            </div>
            <div class="kpi" style="border-color:#bbf7d0;">
                <div class="kpi-lbl">Total Revenue</div>
                <div class="kpi-val c-green <?php echo e(kpiSizeClass($revStr)); ?>"><?php echo e($revStr); ?></div>
                <div class="kpi-hint">money collected</div>
            </div>
            <div class="kpi" style="border-color:#fed7aa;">
                <div class="kpi-lbl">Total Guests</div>
                <div class="kpi-val c-orange"><?php echo e(number_format($summary->total_guests)); ?></div>
                <div class="kpi-hint">people served</div>
            </div>
            <div class="kpi" style="border-color:#fecaca;">
                <div class="kpi-lbl">Total Discounts Given</div>
                <div class="kpi-val c-red <?php echo e(kpiSizeClass($discStr)); ?>"><?php echo e($discStr); ?></div>
                <div class="kpi-hint">deducted from sales</div>
            </div>
        </div>
        <div class="kpis kpis-3">
            <div class="kpi">
                <div class="kpi-lbl">Average Order Value</div>
                <div class="kpi-val c-amber <?php echo e(kpiSizeClass($avgStr)); ?>"><?php echo e($avgStr); ?></div>
                <div class="kpi-hint">per table / order</div>
            </div>
            <div class="kpi">
                <div class="kpi-lbl">Revenue Per Guest</div>
                <div class="kpi-val <?php echo e(kpiSizeClass($rpgStr)); ?>"><?php echo e($rpgStr); ?></div>
                <div class="kpi-hint">per person (per head)</div>
            </div>
            <div class="kpi">
                <div class="kpi-lbl">Average Party Size</div>
                <div class="kpi-val"><?php echo e(number_format($summary->avg_party_size, 1)); ?></div>
                <div class="kpi-hint">guests per table</div>
            </div>
        </div>
    </div>

    <div class="section-divider"></div>

    
    <div class="stitle"><span class="stitle-num" style="background:#166534;">₱</span> Revenue vs Cost (Profit &amp; Loss)</div>
    <div class="sbody">
        
        <div class="pnl-verdict <?php echo e($grossProfit >= 0 ? 'pnl-verdict-gain' : 'pnl-verdict-loss'); ?>">
            <div class="pnl-verdict-icon"><?php echo e($grossProfit >= 0 ? '📈' : '📉'); ?></div>
            <div class="pnl-verdict-body">
                <div class="pnl-verdict-label"><?php echo e($grossProfit >= 0 ? '✅ NET GAIN — You are profitable this period' : '❌ NET LOSS — Costs exceeded revenue this period'); ?></div>
                <div class="pnl-verdict-amount"><?php echo e($grossProfit >= 0 ? '+' : '−'); ?>₱<?php echo e(number_format(abs($grossProfit), 2)); ?></div>
                <div class="pnl-verdict-sub">
                    Gross margin: <strong><?php echo e($grossMarginPct); ?>%</strong>
                    &nbsp;·&nbsp;
                    Revenue: <strong>₱<?php echo e(number_format($summary->total_revenue, 2)); ?></strong>
                    &nbsp;·&nbsp;
                    Total cost: <strong>₱<?php echo e(number_format($totalCogs, 2)); ?></strong>
                </div>
            </div>
            <div class="pnl-verdict-margin">
                <div class="pnl-verdict-margin-ring <?php echo e($grossMarginPct >= 50 ? 'ring-good' : ($grossMarginPct >= 25 ? 'ring-ok' : 'ring-bad')); ?>">
                    <span><?php echo e($grossMarginPct); ?>%</span>
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

        
        <div class="pnl-banner">
            <div class="pnl-col pnl-col-rev">
                <div class="pnl-col-icon">📥</div>
                <div class="pnl-col-lbl">Total Revenue Earned</div>
                <div class="pnl-col-val pnl-green">₱<?php echo e(number_format($summary->total_revenue, 2)); ?></div>
                <div class="pnl-col-hint">Money collected from customers</div>
            </div>
            <div class="pnl-minus">−</div>
            <div class="pnl-col pnl-col-cost">
                <div class="pnl-col-icon">📦</div>
                <div class="pnl-col-lbl">Cost of Goods Sold (COGS)</div>
                <div class="pnl-col-val pnl-red">₱<?php echo e(number_format($totalCogs, 2)); ?></div>
                <div class="pnl-col-hint">Estimated ingredient/stock cost used</div>
            </div>
            <div class="pnl-equals">=</div>
            <div class="pnl-col pnl-col-profit <?php echo e($grossProfit >= 0 ? 'pnl-col-profit-pos' : 'pnl-col-profit-neg'); ?>">
                <div class="pnl-col-icon"><?php echo e($grossProfit >= 0 ? '🏆' : '⚠️'); ?></div>
                <div class="pnl-col-lbl">Gross Profit</div>
                <div class="pnl-col-val <?php echo e($grossProfit >= 0 ? 'pnl-green' : 'pnl-red'); ?>">
                    <?php echo e($grossProfit >= 0 ? '' : '−'); ?>₱<?php echo e(number_format(abs($grossProfit), 2)); ?>

                </div>
                <div class="pnl-col-hint">
                    <span class="pnl-margin-badge <?php echo e($grossMarginPct >= 50 ? 'pnl-badge-good' : ($grossMarginPct >= 25 ? 'pnl-badge-ok' : 'pnl-badge-warn')); ?>">
                        <?php echo e($grossMarginPct); ?>% margin
                    </span>
                </div>
            </div>
        </div>

        
        <div class="pnl-breakdown">
            <div class="pnl-row">
                <div class="pnl-row-icon">🧾</div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Revenue from Packages & Add-ons</div>
                    <div class="pnl-row-desc">Money collected from dine-in customers (after discounts applied)</div>
                </div>
                <div class="pnl-row-val pnl-green">₱<?php echo e(number_format($summary->total_revenue, 2)); ?></div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalExtrasRevenue > 0): ?>
            <div class="pnl-row">
                <div class="pnl-row-icon">🥤</div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Revenue from Extras (Drinks, etc.)</div>
                    <div class="pnl-row-desc">Already included in revenue above — shown separately for clarity</div>
                </div>
                <div class="pnl-row-val" style="color:#1D4ED8;">₱<?php echo e(number_format($totalExtrasRevenue, 2)); ?></div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalExtrasCost > 0): ?>
            <div class="pnl-row">
                <div class="pnl-row-icon">🏷️</div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Cost of Extras Sold</div>
                    <div class="pnl-row-desc">Included in COGS below — extras gross profit: ₱<?php echo e(number_format($totalExtrasProfit, 2)); ?> (<?php echo e($extrasMarginPct); ?>% margin)</div>
                </div>
                <div class="pnl-row-val pnl-orange">− ₱<?php echo e(number_format($totalExtrasCost, 2)); ?></div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="pnl-row">
                <div class="pnl-row-icon">🥩</div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Cost of Stock Used for Sales</div>
                    <div class="pnl-row-desc">Estimated ingredient cost auto-deducted when orders were processed</div>
                </div>
                <div class="pnl-row-val pnl-red">− ₱<?php echo e(number_format($salesCogs, 2)); ?></div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($manualCogs > 0): ?>
            <div class="pnl-row">
                <div class="pnl-row-icon">📋</div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Manual Stock Removals</div>
                    <div class="pnl-row-desc">Stock manually removed (waste, spoilage, corrections, etc.)</div>
                </div>
                <div class="pnl-row-val pnl-orange">− ₱<?php echo e(number_format($manualCogs, 2)); ?></div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="pnl-row">
                <div class="pnl-row-icon">📉</div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Total Discounts Given</div>
                    <div class="pnl-row-desc">SC, PWD, and other discounts deducted from full price (already reflected in revenue above)</div>
                </div>
                <div class="pnl-row-val pnl-orange">− ₱<?php echo e(number_format($summary->total_discounts, 2)); ?></div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($restockCost > 0): ?>
            <div class="pnl-row pnl-row-restock">
                <div class="pnl-row-icon">🚚</div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Stock Restocking Spend <span style="font-size:10px;font-weight:500;color:#6B7280;">(informational)</span></div>
                    <div class="pnl-row-desc">Estimated cost of stock deliveries received this period — not deducted from profit above</div>
                </div>
                <div class="pnl-row-val pnl-blue">₱<?php echo e(number_format($restockCost, 2)); ?></div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="pnl-row pnl-row-total">
                <div class="pnl-row-icon"><?php echo e($grossProfit >= 0 ? '✅' : '❌'); ?></div>
                <div class="pnl-row-label">
                    <div class="pnl-row-title">Estimated Gross Profit</div>
                    <div class="pnl-row-desc">Revenue minus cost of all goods used — <strong>your margin is <?php echo e($grossMarginPct); ?>%</strong></div>
                </div>
                <div class="pnl-row-val <?php echo e($grossProfit >= 0 ? 'pnl-green' : 'pnl-red'); ?>" style="font-size:18px;font-weight:800;">
                    <?php echo e($grossProfit >= 0 ? '' : '−'); ?>₱<?php echo e(number_format(abs($grossProfit), 2)); ?>

                </div>
            </div>
        </div>

        
        <div class="kpis kpis-3" style="margin-top:16px;">
            <div class="kpi" style="border-color:#bbf7d0;">
                <div class="kpi-lbl">Revenue per Guest</div>
                <div class="kpi-val c-green">₱<?php echo e(number_format($revenuePerGuest, 2)); ?></div>
                <div class="kpi-hint">average earned per person</div>
            </div>
            <div class="kpi" style="border-color:#fecaca;">
                <div class="kpi-lbl">Cost per Guest</div>
                <div class="kpi-val c-red">₱<?php echo e(number_format($cogsPerGuest, 2)); ?></div>
                <div class="kpi-hint">average ingredient cost per person</div>
            </div>
            <div class="kpi" style="border-color:<?php echo e(($revenuePerGuest - $cogsPerGuest) >= 0 ? '#bbf7d0' : '#fecaca'); ?>;">
                <div class="kpi-lbl">Profit per Guest</div>
                <div class="kpi-val <?php echo e(($revenuePerGuest - $cogsPerGuest) >= 0 ? 'c-green' : 'c-red'); ?>">
                    ₱<?php echo e(number_format($revenuePerGuest - $cogsPerGuest, 2)); ?>

                </div>
                <div class="kpi-hint">margin earned per person</div>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalCogs == 0): ?>
        <div class="callout" style="margin-top:12px;">
            ⚠️ <strong>Note:</strong> Cost data is ₱0.00 — this usually means product cost prices haven't been set in the Inventory tab,
            or no stock movements were recorded during this period. Set a <strong>Cost Price</strong> per product in Inventory to see accurate profit figures.
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="section-divider"></div>

    
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
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $paymentBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php $pct = $summary->total_revenue > 0 ? round(($p->total / $summary->total_revenue) * 100, 1) : 0; ?>
                            <tr>
                                <td><span class="badge <?php echo e($p->payment === 'Cash' ? 'b-cash' : 'b-qr'); ?>"><?php echo e($p->payment === 'Cash' ? '💵' : '📱'); ?> <?php echo e($p->payment); ?></span></td>
                                <td class="c tb"><?php echo e($p->count); ?></td>
                                <td class="r tb">₱<?php echo e(number_format($p->total, 2)); ?></td>
                                <td class="r tm"><?php echo e($pct); ?>%</td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="tm" style="text-align:center;padding:20px;">No payment data</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <div class="col-title">📦 Package Popularity <span style="font-size:11px;color:#6B7280;font-weight:500;text-transform:none;">(by number of guests)</span></div>
                <?php $maxPkg = max(array_values($packageCounts) ?: [1]); ?>
                <div class="pkg-box">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $packageCounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="pkg-row">
                        <div class="pkg-lbl"><?php echo e($name); ?></div>
                        <div class="pkg-bar-bg">
                            <div class="pkg-bar pb-<?php echo e(strtolower($name)); ?>" style="width:<?php echo e($maxPkg > 0 ? round(($count/$maxPkg)*100) : 0); ?>%;"></div>
                        </div>
                        <div class="pkg-ct"><?php echo e(number_format($count)); ?> pax</div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="section-divider"></div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($revenueByDay->count() > 0): ?>
    <div class="stitle">
        <span class="stitle-num">3</span> Daily Revenue
        <span class="stitle-sub"><?php echo e($revenueByDay->count()); ?> days</span>
    </div>
    <div class="sbody">
        <div class="twrap">
            <table>
                <thead><tr>
                    <th>Date</th><th class="c">Orders</th><th class="c">Guests</th>
                    <th class="r">Total Revenue</th><th class="r">Revenue / Order</th><th class="r">Revenue / Guest</th>
                </tr></thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $revenueByDay; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="tn"><?php echo e(\Carbon\Carbon::parse($day->day)->format('D, M j, Y')); ?></td>
                        <td class="c"><?php echo e($day->orders); ?></td>
                        <td class="c"><?php echo e($day->guests ?? 0); ?></td>
                        <td class="r tb">₱<?php echo e(number_format($day->revenue, 2)); ?></td>
                        <td class="r tm">₱<?php echo e($day->orders > 0 ? number_format($day->revenue / $day->orders, 2) : '—'); ?></td>
                        <td class="r tm">₱<?php echo e(($day->guests ?? 0) > 0 ? number_format($day->revenue / $day->guests, 2) : '—'); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
                <?php
                    $dayTotalOrders  = $revenueByDay->sum('orders');
                    $dayTotalGuests  = $revenueByDay->sum('guests');
                    $dayTotalRevenue = $revenueByDay->sum('revenue');
                ?>
                <tfoot><tr>
                    <td class="tot-label"><?php echo e($revenueByDay->count()); ?>-Day Total</td>
                    <td class="c"><?php echo e(number_format($dayTotalOrders)); ?></td>
                    <td class="c"><?php echo e(number_format($dayTotalGuests)); ?></td>
                    <td class="r tot-hi">₱<?php echo e(number_format($dayTotalRevenue, 2)); ?></td>
                    <td class="r tot-amber">₱<?php echo e($dayTotalOrders > 0 ? number_format($dayTotalRevenue / $dayTotalOrders, 2) : '—'); ?></td>
                    <td class="r tot-amber">₱<?php echo e($dayTotalGuests > 0 ? number_format($dayTotalRevenue / $dayTotalGuests, 2) : '—'); ?></td>
                </tr></tfoot>
            </table>
        </div>
    </div>
    <div class="section-divider"></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="stitle"><span class="stitle-num">4</span> Busiest Hours of the Day</div>
    <div class="sbody">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($peakHours->isEmpty()): ?>
            <div class="nodata">No hourly data available for this period.</div>
        <?php else: ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($busiestHour): ?>
            <div class="peak-highlight">
                🔥 &nbsp;<strong>Busiest Hour:</strong> &nbsp;<?php echo e($busiestHour['label']); ?>

                &nbsp;·&nbsp; <?php echo e($busiestHour['orders']); ?> orders
                &nbsp;·&nbsp; ₱<?php echo e(number_format($busiestHour['revenue'], 2)); ?> earned
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="twrap">
                <table>
                    <thead><tr>
                        <th>Hour of Day</th><th class="c">No. of Orders</th><th class="c">Guests</th>
                        <th class="r">Revenue Earned</th><th class="r">Avg per Order</th>
                    </tr></thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $peakHours->sortByDesc('revenue'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="tn"><?php echo e($hour['label']); ?></td>
                            <td class="c"><?php echo e($hour['orders']); ?></td>
                            <td class="c"><?php echo e($hour['guests']); ?></td>
                            <td class="r tb">₱<?php echo e(number_format($hour['revenue'], 2)); ?></td>
                            <td class="r tm">₱<?php echo e($hour['orders'] > 0 ? number_format($hour['revenue']/$hour['orders'],2) : '—'); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="section-divider"></div>

    
    <div class="stitle"><span class="stitle-num">5</span> Extras & Add-ons Sold</div>
    <div class="sbody">

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalExtrasRevenue > 0): ?>
        <div class="kpis kpis-3" style="margin-bottom:16px;">
            <div class="kpi" style="border-color:#bbf7d0;">
                <div class="kpi-lbl">Extras Revenue</div>
                <div class="kpi-val c-green kpi-val--lg">₱<?php echo e(number_format($totalExtrasRevenue, 2)); ?></div>
                <div class="kpi-hint">total collected from extras</div>
            </div>
            <div class="kpi" style="border-color:#fecaca;">
                <div class="kpi-lbl">Extras Cost (COGS)</div>
                <div class="kpi-val c-red kpi-val--lg">₱<?php echo e(number_format($totalExtrasCost, 2)); ?></div>
                <div class="kpi-hint">what the store paid for them</div>
            </div>
            <div class="kpi" style="border-color:<?php echo e($totalExtrasProfit >= 0 ? '#bbf7d0' : '#fecaca'); ?>;">
                <div class="kpi-lbl">Extras Gross Profit</div>
                <div class="kpi-val <?php echo e($totalExtrasProfit >= 0 ? 'c-green' : 'c-red'); ?> kpi-val--lg">
                    <?php echo e($totalExtrasProfit >= 0 ? '' : '−'); ?>₱<?php echo e(number_format(abs($totalExtrasProfit), 2)); ?>

                </div>
                <div class="kpi-hint"><?php echo e($extrasMarginPct); ?>% margin on extras</div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="twocol">
            <div>
                <div class="col-title">🥩 Extra Items
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalExtrasRevenue > 0): ?><strong>₱<?php echo e(number_format($totalExtrasRevenue, 2)); ?></strong><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($extrasMap)): ?>
                    <div class="ext-box"><div style="color:#6B7280;font-size:13px;text-align:center;padding:14px 0;">No extra items sold this period.</div></div>
                <?php else: ?>
                
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
                            <?php $rk = 0; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $extrasMap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $rk++;
                                $margin = $d['revenue'] > 0 ? round(($d['profit'] / $d['revenue']) * 100, 1) : 0;
                                $marginCls = $margin >= 40 ? 'tg' : ($margin >= 15 ? 'to' : 'tr');
                            ?>
                            <tr>
                                <td class="c tm">#<?php echo e($rk); ?></td>
                                <td class="tn"><?php echo e($name); ?></td>
                                <td class="c"><?php echo e($d['qty']); ?>×</td>
                                <td class="r tb">₱<?php echo e(number_format($d['revenue'], 2)); ?></td>
                                <td class="r tm">₱<?php echo e(number_format($d['cost'], 2)); ?></td>
                                <td class="r <?php echo e($d['profit'] >= 0 ? 'tg' : 'tr'); ?>">
                                    <?php echo e($d['profit'] >= 0 ? '' : '−'); ?>₱<?php echo e(number_format(abs($d['profit']), 2)); ?>

                                </td>
                                <td class="r <?php echo e($marginCls); ?>"><?php echo e($margin); ?>%</td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                        <tfoot><tr>
                            <td class="tot-label" colspan="2">Total</td>
                            <td class="c"><?php echo e(collect($extrasMap)->sum('qty')); ?>×</td>
                            <td class="r tot-hi">₱<?php echo e(number_format($totalExtrasRevenue, 2)); ?></td>
                            <td class="r tot-amber">₱<?php echo e(number_format($totalExtrasCost, 2)); ?></td>
                            <td class="r tot-hi">₱<?php echo e(number_format($totalExtrasProfit, 2)); ?></td>
                            <td class="r tot-amber"><?php echo e($extrasMarginPct); ?>%</td>
                        </tr></tfoot>
                    </table>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalExtrasCost == 0 && $totalExtrasRevenue > 0): ?>
                <div class="callout" style="margin-top:8px;font-size:12px;">
                    ⚠️ Cost data is ₱0 for all extras — set a <strong>Cost Price</strong> per product in Inventory to see accurate profit figures.
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <div class="col-title">➕ Add-ons
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalAddonsRevenue > 0): ?><strong>₱<?php echo e(number_format($totalAddonsRevenue, 2)); ?></strong><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php $mxA = collect($addonsMap)->max('revenue') ?: 1; $rk = 0; ?>
                <div class="ext-box">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($addonsMap)): ?>
                        <div style="color:#6B7280;font-size:13px;text-align:center;padding:14px 0;">No add-ons sold this period.</div>
                    <?php else: ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $addonsMap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $rk++; $bw = round(($d['revenue']/$mxA)*100); ?>
                        <div class="ext-row">
                            <div class="ext-rank">#<?php echo e($rk); ?></div>
                            <div class="ext-name"><?php echo e($name); ?></div>
                            <div class="ext-bwrap"><div class="ext-bar" style="width:<?php echo e($bw); ?>%;background:#4A90D9;"></div></div>
                            <div class="ext-qty"><?php echo e($d['qty']); ?>×</div>
                            <div class="ext-rev">₱<?php echo e(number_format($d['revenue'], 2)); ?></div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="section-divider"></div>

    
    <div class="stitle"><span class="stitle-num">6</span> Discount Summary</div>
    <div class="sbody">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalDiscountedGuests === 0): ?>
            <div class="nodata">No discounts were applied during this period.</div>
        <?php else: ?>
            <div class="disc-grid" style="margin-bottom:12px;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $discountTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $pg = $summary->total_guests > 0 ? round(($dt['count']/$summary->total_guests)*100,1) : 0; ?>
                <div class="disc-card">
                    <div class="disc-emoji"><?php echo e($dt['emoji']); ?></div>
                    <div class="disc-type"><?php echo e($dt['label']); ?></div>
                    <div class="disc-count" style="color:<?php echo e($dt['color']); ?>;font-size:28px;font-weight:800;line-height:1;"><?php echo e(number_format($dt['count'])); ?></div>
                    <div class="disc-amount">₱<?php echo e(number_format($dt['amount'], 2)); ?> total saved</div>
                    <div class="disc-pct"><?php echo e($pg); ?>% of all guests</div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="disc-card" style="background:#FEF9C3;border-color:#FCD34D;">
                    <div class="disc-emoji">🧾</div>
                    <div class="disc-type">Total Discounted</div>
                    <div class="disc-count"><?php echo e(number_format($totalDiscountedGuests)); ?></div>
                    <div class="disc-amount">₱<?php echo e(number_format($summary->total_discounts, 2)); ?> total</div>
                    <div class="disc-pct">across all discount types</div>
                </div>
            </div>
            <div class="callout" style="border-color:#fbbf24;background:#fffbeb;color:#78350f;">
                ⚠️ <strong>Legal Note:</strong> SC and PWD discounts are mandated by Philippine law (RA 9994 &amp; RA 7277).
                Please keep this report on file for BIR / DTI documentation purposes.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="section-divider"></div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orders->count() > 0): ?>
    <div class="stitle">
        <span class="stitle-num">7</span> Complete Order List
        <span class="stitle-sub"><?php echo e($orders->count()); ?> orders</span>
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="tn">#<?php echo e($o->receipt_number); ?></td>
                        <td class="tm"><?php echo e($o->completed_at->format('M j, Y · g:i A')); ?></td>
                        <td class="c"><?php echo e($o->total_people); ?></td>
                        <td class="tm" style="font-size:12px;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($o->packages)): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $o->packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo e($pk['people']); ?>×<?php echo e($pk['name']); ?><?php echo e(!$loop->last?', ':''); ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>—<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="c"><span class="badge <?php echo e($o->payment==='Cash'?'b-cash':'b-qr'); ?>"><?php echo e($o->payment); ?></span></td>
                        <td class="r tm">₱<?php echo e(number_format($o->subtotal,2)); ?></td>
                        <td class="c">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($o->discount_percent > 0): ?><span class="tr"><?php echo e($o->discount_percent); ?>%</span>
                            <?php else: ?><span class="tm">—</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="r tb">₱<?php echo e(number_format($o->total,2)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
                <?php
                    $grandSubtotal  = $orders->sum('subtotal');
                    $grandDiscounts = $orders->sum('discount_amount');
                    $grandTotal     = $orders->sum('total');
                    $grandGuests    = $orders->sum('total_people');
                ?>
                <tfoot><tr>
                    <td class="tot-label">Grand Total</td>
                    <td></td>
                    <td class="c"><?php echo e(number_format($grandGuests)); ?></td>
                    <td></td>
                    <td class="c tot-label"><?php echo e($orders->count()); ?> orders</td>
                    <td class="r tot-amber">₱<?php echo e(number_format($grandSubtotal, 2)); ?></td>
                    <td class="c tot-amber">₱<?php echo e(number_format($grandDiscounts, 2)); ?></td>
                    <td class="r tot-hi">₱<?php echo e(number_format($grandTotal, 2)); ?></td>
                </tr></tfoot>
            </table>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> 

    <div class="section-divider"></div>

    
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
                <div class="kpi-val c-blue"><?php echo e($inventory->count()); ?></div>
                <div class="kpi-hint">products in inventory</div>
            </div>
            <div class="kpi" style="<?php echo e($lowStockCount > 0 ? 'border-color:#fed7aa;' : 'border-color:#bbf7d0;'); ?>">
                <div class="kpi-lbl">⚠️ Low Stock</div>
                <div class="kpi-val <?php echo e($lowStockCount > 0 ? 'c-orange' : 'c-green'); ?>"><?php echo e($lowStockCount); ?></div>
                <div class="kpi-hint">need to restock soon</div>
            </div>
            <div class="kpi" style="<?php echo e($outOfStockCount > 0 ? 'border-color:#fecaca;' : 'border-color:#bbf7d0;'); ?>">
                <div class="kpi-lbl">🔴 Out of Stock</div>
                <div class="kpi-val <?php echo e($outOfStockCount > 0 ? 'c-red' : 'c-green'); ?>"><?php echo e($outOfStockCount); ?></div>
                <div class="kpi-hint">empty, order immediately</div>
            </div>
            <?php
                $invValStr = '₱' . number_format($totalInventoryValue, 2);
                $invValCls = strlen($invValStr) > 12 ? 'kpi-val--xl' : (strlen($invValStr) > 9 ? 'kpi-val--lg' : '');
            ?>
            <div class="kpi" style="border-color:#bbf7d0;">
                <div class="kpi-lbl">Estimated Total Value</div>
                <div class="kpi-val c-green <?php echo e($invValCls); ?>"><?php echo e($invValStr); ?></div>
                <div class="kpi-hint">cost × current stock</div>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($inventory->isEmpty()): ?>
            <div class="nodata">No inventory items found.</div>
        <?php else: ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $inventoryByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $catValue = $items->sum(fn($i) => (float)$i->stock * (float)$i->cost); ?>
            <div class="twrap" style="margin-bottom:14px;">
                <table>
                    <thead>
                        <tr>
                            <th colspan="9" style="background:#374151;color:#E5E7EB;font-size:10px;letter-spacing:1px;font-weight:700;">
                                📁 <?php echo e(strtoupper($category)); ?> — <?php echo e($items->count()); ?> items &nbsp;·&nbsp; Subtotal: ₱<?php echo e(number_format($catValue, 2)); ?>

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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
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
                        ?>
                        <tr>
                            <td class="tn"><?php echo e($item->name); ?></td>
                            <td class="c tm"><?php echo e($item->unit); ?></td>
                            <td class="r tg"><?php echo e(number_format($openingStk, 2)); ?></td>
                            <td class="r" style="font-weight:700;color:#1D4ED8;"><?php echo e(number_format($closingStk, 2)); ?></td>
                            <td class="r <?php echo e($netCls); ?>"><?php echo e($hadMov ? $netPfx.number_format($netChg,2) : '—'); ?></td>
                            <td class="r tm"><?php echo e(number_format((float)$item->reorder_level, 2)); ?></td>
                            <td class="r tm">₱<?php echo e(number_format((float)$item->cost, 2)); ?></td>
                            <td class="r tb">₱<?php echo e(number_format($ival, 2)); ?></td>
                            <td class="c"><span class="badge <?php echo e($sbadge); ?>"><?php echo e($slabel); ?></span></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="grand-panel">
                <div class="grand-panel-head">📦 &nbsp;Grand Total Inventory Value</div>
                <div class="grand-panel-body">
                    <div class="grand-cell">
                        <div class="grand-cell-lbl">Total Items</div>
                        <div class="grand-cell-val"><?php echo e($inventory->count()); ?></div>
                    </div>
                    <div class="grand-cell">
                        <div class="grand-cell-lbl">Low Stock Items</div>
                        <div class="grand-cell-val" style="<?php echo e($lowStockCount > 0 ? 'color:#fbbf24;' : 'color:#6ee7b7;'); ?>"><?php echo e($lowStockCount); ?></div>
                    </div>
                    <div class="grand-cell">
                        <div class="grand-cell-lbl">Out of Stock</div>
                        <div class="grand-cell-val" style="<?php echo e($outOfStockCount > 0 ? 'color:#f87171;' : 'color:#6ee7b7;'); ?>"><?php echo e($outOfStockCount); ?></div>
                    </div>
                    <div class="grand-cell">
                        <div class="grand-cell-lbl">🏆 Total Inventory Value</div>
                        <div class="grand-cell-val">₱<?php echo e(number_format($totalInventoryValue, 2)); ?></div>
                        <div class="grand-cell-sub">estimated at cost price</div>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="spacer"></div>



    
    <div class="rfooter">
        <div>
            <div style="font-weight:700;color:#111827;margin-bottom:3px;">SamgyHann 199 POS &nbsp;·&nbsp; Olongapo City, Zambales</div>
            <div>Period: <strong><?php echo e($periodLabel); ?></strong> &nbsp;(<?php echo e($reportStartDate); ?> – <?php echo e($reportEndDate); ?>)</div>
        </div>
        <div style="text-align:right;">
            <div>Printed: <strong><?php echo e($printedOn); ?></strong></div>
            <div>Prepared by: <strong><?php echo e($generatedBy); ?></strong></div>
        </div>
    </div>

    
    <div class="sbody" style="padding-top:0;padding-bottom:32px;">
        <div class="sig-grid">
            <div>
                <div class="sig-line">
                    <div class="sig-name"><?php echo e($generatedBy); ?></div>
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

</div>


<div class="pgfooter">
    <span>SamgyHann 199 &nbsp;·&nbsp; Sales Report &nbsp;·&nbsp; <?php echo e($periodLabel); ?></span>
    <span>Printed: <?php echo e($printedOn); ?></span>
</div>

</body>
</html>
<?php /**PATH C:\Users\Mark\samgyeopsal-pos - Copy\resources\views/reports/sales.blade.php ENDPATH**/ ?>