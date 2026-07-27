<?php

declare(strict_types=1);

function assertCodepad(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$module = (string) file_get_contents($root . '/OpenHomeAlarm/module.php');
$html = (string) file_get_contents($root . '/OpenHomeAlarm/visualization/index.html');
$css = (string) file_get_contents($root . '/OpenHomeAlarm/visualization/style.css');
$javascript = (string) file_get_contents($root . '/OpenHomeAlarm/visualization/app.js');
$locale = json_decode(
    (string) file_get_contents($root . '/OpenHomeAlarm/locale.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);

assertCodepad(
    str_contains($module, "case 'DisarmWithCode':"),
    'The visualization RequestAction gateway must accept DisarmWithCode.'
);
assertCodepad(
    str_contains($module, '$this->DisarmWithCode($Value)'),
    'Visualization code submission must use the existing DisarmWithCode API.'
);
assertCodepad(
    str_contains($module, "'Type'    => 'disarm_code'") && str_contains($module, "'Success' => false"),
    'Rejected visualization codes must produce codepad feedback without exposing the code.'
);
assertCodepad(
    str_contains($module, 'private function PublishVisualizationState(?array $interaction = null): void'),
    'Visualization state publishing must support transient interaction feedback.'
);

assertCodepad(str_contains($html, 'id="codepadOverlay"'), 'The visualization must contain a codepad overlay.');
assertCodepad(str_contains($html, 'id="inlineCodepad"'), 'Wide visualizations must contain a permanently integrated codepad.');
assertCodepad(str_contains($html, 'id="inlineCodepadDisplay"'), 'The integrated codepad must provide its own protected code display.');
assertCodepad(str_contains($html, 'data-code-surface="inline"'), 'Integrated codepad controls must be explicitly addressable as the inline surface.');
assertCodepad(str_contains($html, 'role="dialog"'), 'The codepad must be exposed as a dialog.');
assertCodepad(substr_count($html, 'data-code-digit=') === 20, 'Inline and popup codepads must each provide digits 0 through 9.');
assertCodepad(str_contains($html, 'id="codepadDelete"'), 'The codepad must provide a delete key.');
assertCodepad(str_contains($html, 'id="codepadClear"'), 'The popup codepad must provide a clear key.');
assertCodepad(str_contains($html, 'id="inlineCodepadClear"'), 'The inline codepad must provide a clear key.');
assertCodepad(str_contains($html, 'id="codepadConfirm"'), 'The popup codepad must provide an explicit disarm button.');
assertCodepad(str_contains($html, 'id="inlineCodepadConfirm"'), 'The inline codepad must provide an explicit disarm button.');
assertCodepad(substr_count($html, 'data-code-confirm') === 2, 'Both codepad surfaces must provide an explicit disarm action.');
assertCodepad(substr_count($html, 'class="oha-code-dot"') === 16, 'Both code displays must support up to eight digits.');

assertCodepad(
    str_contains($javascript, "ohaRequestAction('DisarmWithCode', code);"),
    'The codepad must submit the entered code through the HTML-SDK RequestAction channel.'
);
assertCodepad(
    str_contains($javascript, 'ohaCodeBuffer += digit;'),
    'Each accepted digit must be appended to the existing code buffer instead of replacing it.'
);
assertCodepad(
    str_contains($html, 'onclick="ohaAppendCodeDigit(\'1\')"')
        && str_contains($html, 'onclick="ohaSubmitCode()"')
        && str_contains($html, 'onclick="ohaDeleteCodeDigit()"'),
    'Codepad controls must invoke their handlers directly from the rendered HTML.'
);
assertCodepad(
    !str_contains($javascript, "document.addEventListener('pointerup'")
        && !str_contains($javascript, "button.addEventListener('click'"),
    'Codepad input must not depend on delegated or post-render listener registration.'
);
assertCodepad(
    !str_contains($javascript, 'button.disabled = !inputAllowed')
        && str_contains($javascript, 'button.dataset.enabled = digitEnabled'),
    'Codepad keys must remain native clickable buttons and use state data instead of disabled attributes.'
);
assertCodepad(
    str_contains($javascript, 'function ohaClearCodeEntry()'),
    'The codepad must provide a dedicated clear operation.'
);
assertCodepad(
    str_contains($javascript, 'ohaState?.Capabilities?.CodeRequired'),
    'The codepad must only open when code protection is enabled.'
);
assertCodepad(
    str_contains($javascript, 'ohaCodeBuffer.length < 4') && str_contains($javascript, 'ohaCodeBuffer.length >= 8'),
    'The codepad must enforce the configured 4 to 8 digit input range in the UI.'
);
assertCodepad(
    str_contains($javascript, "ohaTranslate('Code not accepted. Please try again.')"),
    'Rejected codes must be reported directly in the codepad.'
);
assertCodepad(
    !str_contains($javascript, 'localStorage') && !str_contains($javascript, 'sessionStorage'),
    'The disarm code must never be persisted in browser storage.'
);
assertCodepad(
    !str_contains($module, "RegisterVariableString('DisarmCode") && !str_contains($module, 'IDENT_DISARM_CODE_INPUT'),
    'The submitted code must not be stored in a Symcon variable.'
);
assertCodepad(
    !str_contains($javascript, 'button.disabled = codeRequired;'),
    'Code protection must open the codepad instead of disabling the disarm button.'
);

assertCodepad(str_contains($css, '.oha-inline-codepad'), 'The permanently integrated codepad must be styled.');
assertCodepad(str_contains($css, '.oha-inline-disarm-button'), 'The integrated codepad must style its explicit disarm button.');
assertCodepad(str_contains($css, '@media (min-width: 900px)'), 'The integrated codepad must use a tile-width breakpoint.');
assertCodepad(str_contains($css, '.oha-control-bar[data-code-required="true"]'), 'The popup trigger must be hidden on wide code-protected views.');
assertCodepad(str_contains($javascript, 'function ohaRenderInlineCodepad(state)'), 'The inline codepad must be rendered from the current control state.');
assertCodepad(str_contains($javascript, 'function ohaCodeInputAllowed()'), 'Both codepad surfaces must share the same backend-driven enablement rule.');
assertCodepad(str_contains($javascript, "window.matchMedia('(min-width: 900px)')"), 'Switching to the wide layout must close a previously opened popup codepad.');
assertCodepad(str_contains($css, '.oha-codepad-overlay'), 'The codepad overlay must be styled.');
assertCodepad(str_contains($css, '.oha-code-grid'), 'The numeric code grid must be styled.');
assertCodepad(str_contains($css, '@media (max-width: 420px)'), 'The codepad must have a mobile layout.');

$translations = $locale['translations']['de'] ?? [];
foreach ([
    'Disarm system',
    'Enter the 4 to 8 digit disarm code.',
    'Cancel code entry',
    'Delete last digit',
    'Clear code entry',
    'Confirm code',
    'Code pad',
    'Code entry',
    'digits entered',
    'Code not accepted. Please try again.',
    'No response from the alarm system. Please try again.',
    'Inactive',
    'Code protection is not enabled.',
    'Code entry becomes available when disarming is possible.',
    'Deactivate',
    'Activate',
    'Blocked',
    'Deactivate first to change mode'
] as $translationKey) {
    assertCodepad(
        isset($translations[$translationKey]),
        'Missing German codepad translation for ' . $translationKey . '.'
    );
}

fwrite(STDOUT, "OpenHomeAlarm visualization codepad checks passed.\n");
