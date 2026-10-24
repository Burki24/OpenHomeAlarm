<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$output = $root . '/OpenHomeAlarm/visualization';

function escapeSVG(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function pin(int $number, int $x, int $y): string
{
    return '<g><circle class="pin" cx="' . $x . '" cy="' . $y . '" r="17"/>'
        . '<text class="number" x="' . $x . '" y="' . $y . '">' . $number . '</text></g>';
}

/** @param list<string> $labels */
function legend(array $labels, int $x, int $y, int $step, array $reserved = []): string
{
    $svg = '';
    foreach ($labels as $index => $label) {
        $number = $index + 1;
        $lineY = $y + ($index * $step);
        $suffix = in_array($number, $reserved, true) ? ' <tspan class="reserved">(' . ($GLOBALS['guideGerman'] ? 'reserviert' : 'reserved') . ')</tspan>' : '';
        $svg .= pin($number, $x, $lineY - 6);
        $svg .= '<text class="legend" x="' . ($x + 28) . '" y="' . $lineY . '">' . escapeSVG($label) . $suffix . '</text>';
    }

    return $svg;
}

function baseStyle(): string
{
    return '<defs><style>'
        . 'text{font-family:Arial,sans-serif;fill:#172033}.title{font-size:31px;font-weight:700}.subtitle{font-size:16px;fill:#657186}'
        . '.eyebrow{font-size:12px;font-weight:700;letter-spacing:1px;fill:#657186}.heading{font-size:25px;font-weight:700}'
        . '.body{font-size:16px}.small{font-size:14px;fill:#657186}.button{font-size:15px;font-weight:700}.legend{font-size:17px}'
        . '.reserved{fill:#8d98a9;font-size:14px}.pin{fill:#172033;stroke:#fff;stroke-width:3}.number{font-size:14px;fill:#fff;font-weight:700;text-anchor:middle;dominant-baseline:central}'
        . '.card{fill:#fff;stroke:#cbd4e1;stroke-width:2}.control{fill:#fff;stroke:#cbd4e1;stroke-width:2}'
        . '</style></defs>';
}

function tileSVG(bool $german): string
{
    $GLOBALS['guideGerman'] = $german;
    $labels = $german
        ? ['Hintergrund', 'Primärer Text', 'Überschriften', 'Unterüberschriften und Sekundärtext', 'Akzent', 'Information', 'Erfolg', 'Warnung', 'Fehler und Gefahr']
        : ['Background', 'Primary text', 'Headings', 'Subheadings and secondary text', 'Accent', 'Information', 'Success', 'Warning', 'Error and danger'];
    $title = $german ? 'OpenHomeAlarm-Kachel' : 'OpenHomeAlarm tile';
    $subtitle = $german
        ? 'Schematische Zuordnung der Felder unter „Eigene Kachelfarben verwenden“'
        : 'Schematic mapping of the fields below “Use custom tile colors”';
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1500" height="850" viewBox="0 0 1500 850"><title>' . escapeSVG($title) . '</title>' . baseStyle();
    $svg .= '<rect width="1500" height="850" rx="22" fill="#fff"/><text x="45" y="56" class="title">' . escapeSVG($title) . '</text><text x="45" y="84" class="subtitle">' . escapeSVG($subtitle) . '</text>';
    $svg .= '<rect x="45" y="112" width="900" height="680" rx="18" fill="#edf1f6"/>';
    $svg .= '<rect x="70" y="137" width="850" height="128" rx="16" fill="#e4f3eb" stroke="#bbd9c8" stroke-width="2"/>';
    $svg .= '<circle cx="111" cy="185" r="24" fill="#d0eadb"/><path d="M111 170l13 5v11c0 11-7 18-13 21-6-3-13-10-13-21v-11z" fill="none" stroke="#3d9163" stroke-width="3"/>';
    $svg .= '<text x="151" y="168" class="eyebrow">' . ($german ? 'SICHERHEITSSTATUS' : 'SECURITY STATUS') . '</text><text x="151" y="207" class="heading" fill="#3d9163">' . ($german ? 'Scharf' : 'Armed') . '</text><text x="151" y="235" class="small">' . ($german ? 'Die Überwachung ist aktiv.' : 'Monitoring is active.') . '</text>';
    $svg .= '<rect x="70" y="287" width="850" height="112" rx="14" class="card"/><text x="91" y="316" class="eyebrow">' . ($german ? 'ALARMBEREICH' : 'ALARM AREA') . '</text><text x="91" y="343" class="body" font-weight="700">' . ($german ? 'Alarmbereich auswählen' : 'Select alarm area') . '</text>';
    $svg .= '<rect x="91" y="359" width="138" height="28" rx="14" fill="#d9f0e4" stroke="#4cb88a" stroke-width="3"/><text x="111" y="379" class="button" fill="#2d8c66">Main area</text>';
    $svg .= '<rect x="241" y="359" width="104" height="28" rx="14" class="control"/><text x="262" y="379" class="button">Garage</text><rect x="357" y="359" width="100" height="28" rx="14" class="control"/><text x="379" y="379" class="button">Garten</text>';
    $svg .= '<rect x="70" y="421" width="850" height="218" rx="14" class="card"/><text x="91" y="451" class="eyebrow">' . ($german ? 'SCHARFMODI' : 'ARMING MODES') . '</text><text x="91" y="478" class="body" font-weight="700">' . ($german ? 'Sicherheitsbereiche' : 'Security modes') . '</text>';
    $cards = [[91, '#ffffff', '#3d9163', $german ? 'Zuhause' : 'Home', $german ? 'Bereit' : 'Ready'], [371, '#fff8e6', '#b97c12', $german ? 'Abwesend' : 'Away', $german ? 'Nicht bereit' : 'Not ready'], [651, '#fff0ed', '#c8402b', $german ? 'Nacht' : 'Night', $german ? 'Alarm' : 'Alarm']];
    foreach ($cards as [$x, $fill, $color, $name, $state]) {
        $svg .= '<rect x="' . $x . '" y="501" width="248" height="112" rx="13" fill="' . $fill . '" stroke="#cbd4e1" stroke-width="2"/><rect x="' . $x . '" y="501" width="6" height="112" rx="3" fill="' . $color . '"/><text x="' . ($x + 24) . '" y="532" class="button">' . escapeSVG($name) . '</text><text x="' . ($x + 24) . '" y="558" class="small" style="fill:' . $color . '">' . escapeSVG($state) . '</text><text x="' . ($x + 24) . '" y="586" class="small">' . ($german ? 'Keine blockierenden Sensoren' : 'No blocking sensors') . '</text>';
    }
    $svg .= '<rect x="70" y="661" width="263" height="96" rx="13" class="card"/><text x="91" y="692" class="eyebrow">' . ($german ? 'ÜBERWACHUNG' : 'MONITORING') . '</text><text x="91" y="726" class="body" font-weight="700">' . ($german ? 'Aktiv' : 'Active') . '</text>';
    $svg .= '<rect x="351" y="661" width="263" height="96" rx="13" class="card"/><text x="372" y="692" class="eyebrow">' . ($german ? 'SYSTEMSTATUS' : 'SYSTEM STATUS') . '</text><text x="372" y="726" class="body" font-weight="700">' . ($german ? 'Keine Störung' : 'No fault') . '</text>';
    $svg .= '<rect x="632" y="661" width="288" height="96" rx="13" class="card"/><text x="653" y="692" class="eyebrow">' . ($german ? 'ALARMSPEICHER' : 'ALARM MEMORY') . '</text><text x="653" y="726" class="body" font-weight="700">' . ($german ? 'Leer' : 'Empty') . '</text>';
    foreach ([[1, 58, 770], [2, 485, 720], [3, 245, 202], [4, 300, 235], [5, 218, 356], [7, 324, 544], [8, 572, 544], [9, 850, 544]] as [$n,$x,$y]) {
        $svg .= pin($n, $x, $y);
    }
    $svg .= '<text x="990" y="145" class="title">' . ($german ? 'Farbfelder' : 'Color fields') . '</text>' . legend($labels, 1010, 190, 61, [6]);
    $svg .= '<text x="990" y="772" class="subtitle">' . ($german ? 'Abgeleitete Hellfarben und Kontraste entstehen automatisch.' : 'Derived soft colors and contrasts are created automatically.') . '</text></svg>';
    return $svg . "\n";
}

function ipsViewSVG(bool $german): string
{
    $GLOBALS['guideGerman'] = $german;
    $labels = $german
        ? ['View-Hintergrund', 'Seitenhintergrund', 'Label-Hintergrund', 'Bedienelement-Hintergrund', 'Aktiver Bedienelement-Hintergrund', 'Inaktiver Bedienelement-Hintergrund', 'Primäre Schrift', 'Aktive Schrift', 'Inaktive Schrift', 'Label-Schrift', 'Iconfarbe', 'Rahmenfarbe', 'Linienfarbe', 'Popup-Hintergrund', 'Popup-Rahmen', 'Akzentfarbe', 'Informationsfarbe', 'Positiver Status', 'Warnstatus', 'Kritischer Status', 'Schattenfarbe']
        : ['View background', 'Page background', 'Label background', 'Control background', 'Active control background', 'Inactive control background', 'Primary text', 'Active text', 'Inactive text', 'Label text', 'Icon color', 'Border color', 'Line color', 'Popup background', 'Popup border', 'Accent color', 'Information color', 'Positive status', 'Warning status', 'Critical status', 'Shadow color'];
    $title = $german ? 'OpenHomeAlarm-IPSView' : 'OpenHomeAlarm IPSView';
    $subtitle = $german ? 'Schematische Zuordnung der universellen IPSView-Farbfelder' : 'Schematic mapping of the universal IPSView color fields';
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1700" height="1120" viewBox="0 0 1700 1120"><title>' . escapeSVG($title) . '</title>' . baseStyle();
    $svg .= '<rect width="1700" height="1120" rx="22" fill="#fff"/><text x="38" y="55" class="title">' . escapeSVG($title) . '</text><text x="38" y="83" class="subtitle">' . escapeSVG($subtitle) . '</text>';
    $svg .= '<rect x="38" y="112" width="1032" height="958" rx="17" fill="#dfe5ee"/><rect x="62" y="136" width="984" height="136" rx="15" fill="#ecf7f1" stroke="#bdc9d8" stroke-width="2" filter="drop-shadow(0 7px 8px rgba(26,35,52,.16))"/>';
    $svg .= '<rect x="83" y="160" width="55" height="55" rx="14" fill="#d8eee2"/><path d="M110 172l14 6v12c0 12-7 19-14 23-7-4-14-11-14-23v-12z" fill="none" stroke="#3d9163" stroke-width="3"/><text x="158" y="171" class="eyebrow">' . ($german ? 'SICHERHEITSSTATUS' : 'SECURITY STATUS') . '</text><text x="158" y="211" class="heading" fill="#3d9163">' . ($german ? 'Scharf' : 'Armed') . '</text><text x="158" y="240" class="small">' . ($german ? 'Die Überwachung ist aktiv.' : 'Monitoring is active.') . '</text>';
    $svg .= '<rect x="62" y="295" width="984" height="126" rx="14" fill="#f7f9fc" stroke="#bdc9d8" stroke-width="2"/><rect x="81" y="312" width="182" height="28" rx="8" fill="#dce6f2"/><text x="94" y="332" class="eyebrow">' . ($german ? 'ALARMBEREICH' : 'ALARM AREA') . '</text><text x="81" y="367" class="body" font-weight="700">' . ($german ? 'Alarmbereich auswählen' : 'Select alarm area') . '</text>';
    $svg .= '<rect x="81" y="382" width="146" height="27" rx="14" fill="#d9f0e4" stroke="#4cb88a" stroke-width="3"/><text x="101" y="401" class="button" fill="#2d8c66">Main area</text><rect x="239" y="382" width="110" height="27" rx="14" class="control"/><text x="260" y="401" class="button">Garage</text><rect x="361" y="382" width="112" height="27" rx="14" fill="#e6e9ef" stroke="#c7ced9"/><text x="384" y="401" class="button" fill="#98a1af">Garten</text>';
    $svg .= '<rect x="62" y="444" width="984" height="252" rx="14" fill="#f7f9fc" stroke="#bdc9d8" stroke-width="2"/><text x="81" y="474" class="eyebrow">' . ($german ? 'SCHARFMODI' : 'ARMING MODES') . '</text><text x="81" y="505" class="body" font-weight="700">' . ($german ? 'Sicherheitsbereiche' : 'Security modes') . '</text>';
    $svg .= '<rect x="81" y="532" width="280" height="135" rx="14" fill="#fff" stroke="#bdc9d8" stroke-width="2"/><text x="107" y="568" class="button">' . ($german ? 'Zuhause' : 'Home') . '</text><text x="107" y="598" class="small" style="fill:#3d9163">' . ($german ? 'Bereit' : 'Ready') . '</text><text x="107" y="629" class="small">' . ($german ? 'Keine blockierenden Sensoren' : 'No blocking sensors') . '</text>';
    $svg .= '<rect x="381" y="532" width="280" height="135" rx="14" fill="#d7e2f6" stroke="#4f6fc8" stroke-width="4"/><text x="407" y="568" class="button" fill="#284a9c">' . ($german ? 'Abwesend' : 'Away') . '</text><text x="407" y="598" class="small" style="fill:#284a9c">' . ($german ? 'Aktiv' : 'Active') . '</text><text x="407" y="629" class="small">' . ($german ? 'Überwachung läuft' : 'Monitoring active') . '</text>';
    $svg .= '<rect x="681" y="532" width="280" height="135" rx="14" fill="#eceff3" stroke="#c7ced9" stroke-width="2"/><text x="707" y="568" class="button" fill="#98a1af">' . ($german ? 'Nacht' : 'Night') . '</text><text x="707" y="598" class="small" style="fill:#98a1af">' . ($german ? 'Deaktiviert' : 'Disabled') . '</text>';
    $svg .= '<rect x="62" y="719" width="984" height="319" rx="14" fill="#f7f9fc" stroke="#bdc9d8" stroke-width="2"/><rect x="81" y="741" width="300" height="55" rx="12" class="card"/><text x="103" y="765" class="eyebrow">' . ($german ? 'ÜBERWACHUNG' : 'MONITORING') . '</text><text x="103" y="786" class="button">' . ($german ? 'Aktiv' : 'Active') . '</text>';
    $svg .= '<rect x="399" y="741" width="300" height="55" rx="12" class="card"/><text x="421" y="765" class="eyebrow">' . ($german ? 'SYSTEMSTATUS' : 'SYSTEM STATUS') . '</text><text x="421" y="786" class="button">' . ($german ? 'Keine Störung' : 'No fault') . '</text>';
    $svg .= '<rect x="717" y="741" width="300" height="55" rx="12" class="card"/><text x="739" y="765" class="eyebrow">' . ($german ? 'ALARMSPEICHER' : 'ALARM MEMORY') . '</text><text x="739" y="786" class="button">' . ($german ? 'Leer' : 'Empty') . '</text>';
    $svg .= '<text x="81" y="842" class="eyebrow">' . ($german ? 'SYSTEMPROTOKOLL' : 'SYSTEM LOG') . '</text><text x="81" y="875" class="button">' . ($german ? 'Anlage scharfgeschaltet' : 'Alarm system armed') . '</text><line x1="81" y1="897" x2="1017" y2="897" stroke="#b7c1cf" stroke-width="2"/><text x="81" y="929" class="button">' . ($german ? 'Ausgangsverzögerung gestartet' : 'Exit delay started') . '</text><line x1="81" y1="951" x2="1017" y2="951" stroke="#b7c1cf" stroke-width="2"/>';
    $svg .= '<rect x="650" y="817" width="347" height="195" rx="16" fill="#fffdf8" stroke="#8b98ab" stroke-width="3" filter="drop-shadow(0 10px 12px rgba(26,35,52,.25))"/><text x="675" y="852" class="eyebrow">' . ($german ? 'SICHERHEITSZENTRALE' : 'SECURITY CENTER') . '</text><text x="675" y="883" class="button">' . ($german ? 'Code eingeben' : 'Enter code') . '</text><circle cx="690" cy="917" r="7" fill="#657186"/><circle cx="720" cy="917" r="7" fill="#657186"/><circle cx="750" cy="917" r="7" fill="#657186"/><circle cx="780" cy="917" r="7" fill="#657186"/><rect x="675" y="947" width="296" height="42" rx="9" fill="#fff0ed" stroke="#c8402b"/><text x="746" y="974" class="button" fill="#c8402b">' . ($german ? 'Deaktivieren' : 'Disarm') . '</text>';
    foreach ([[1, 51, 1040], [2, 92, 1010], [3, 80, 324], [4, 320, 394], [5, 640, 598], [6, 900, 598], [7, 530, 875], [8, 585, 555], [9, 820, 568], [10, 103, 765], [11, 111, 187], [12, 1043, 684], [13, 1002, 898], [14, 665, 827], [15, 990, 819], [16, 210, 383], [18, 340, 545], [19, 947, 476], [20, 949, 650], [21, 633, 1023]] as [$n,$x,$y]) {
        $svg .= pin($n, $x, $y);
    }
    $svg .= '<text x="1123" y="140" class="title">' . ($german ? 'IPSView-Farbfelder' : 'IPSView color fields') . '</text>' . legend($labels, 1140, 184, 42, [17]);
    $svg .= '<text x="1123" y="1071" class="subtitle">' . ($german ? 'Transparenz, Verläufe und Kontraste werden aus diesen Rollen abgeleitet.' : 'Opacity, gradients and contrasts are derived from these roles.') . '</text></svg>';
    return $svg . "\n";
}

foreach (['en' => false, 'de' => true] as $language => $german) {
    foreach (['tile' => tileSVG($german), 'ipsview' => ipsViewSVG($german)] as $type => $svg) {
        $suffix = $german ? '-de' : '';
        $stem = $output . '/color-guide-' . $type . $suffix;
        file_put_contents($stem . '.svg', $svg);
        file_put_contents($stem . '.image', 'data:image/svg+xml;base64,' . base64_encode($svg) . "\n");
    }
}

echo "OpenHomeAlarm color guides generated.\n";
