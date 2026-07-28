'use strict';

document.documentElement.lang = navigator.language || 'en';

let ohaState = window.OHA_INITIAL_STATE ?? null;
let ohaCodeBuffer = '';
let ohaCodeBusy = false;
let ohaCodeRequestTimer = null;
let ohaCodeLockTimer = null;
let ohaIPSViewPollTimer = null;
let ohaIPSViewPendingRequests = 0;
const ohaIPSViewConfig = window.OHA_IPSVIEW && typeof window.OHA_IPSVIEW === 'object'
    ? window.OHA_IPSVIEW
    : null;

let ohaAdaptiveThemeTimer = null;
let ohaAdaptiveThemeObserver = null;
const ohaAdaptiveThemeMedia = window.matchMedia('(prefers-color-scheme: dark)');
const ohaAdaptiveBackgroundProperties = [
    '--ipsview-background',
    '--view-background',
    '--page-background',
    '--content-background',
    '--background-color',
    '--primary-background-color'
];
const ohaAdaptiveSurfaceProperties = [
    '--card-color',
    '--surface-color'
];
const ohaAdaptiveAccentProperties = [
    '--ipsview-accent',
    '--view-accent',
    '--accent-color',
    '--primary-color',
    '--theme-accent'
];

function ohaClamp(value, minimum, maximum) {
    return Math.min(maximum, Math.max(minimum, value));
}

function ohaParseRGBColor(value) {
    const input = String(value ?? '').trim();
    if (input === '' || input === 'transparent') {
        return null;
    }

    const hex = input.match(/^#([0-9a-f]{3,8})$/i);
    if (hex) {
        const raw = hex[1];
        const expanded = raw.length === 3 || raw.length === 4
            ? raw.split('').map((character) => character + character).join('')
            : raw;
        const alpha = expanded.length === 8 ? parseInt(expanded.slice(6, 8), 16) / 255 : 1;
        return {
            red: parseInt(expanded.slice(0, 2), 16),
            green: parseInt(expanded.slice(2, 4), 16),
            blue: parseInt(expanded.slice(4, 6), 16),
            alpha
        };
    }

    const rgb = input.match(/^rgba?\((.+)\)$/i);
    if (!rgb) {
        return null;
    }

    const components = rgb[1].replace(/\//g, ' ').split(/[\s,]+/).filter(Boolean);
    if (components.length < 3) {
        return null;
    }

    const channel = (component) => component.endsWith('%')
        ? ohaClamp(parseFloat(component) * 2.55, 0, 255)
        : ohaClamp(parseFloat(component), 0, 255);
    const alpha = components.length > 3
        ? (components[3].endsWith('%') ? parseFloat(components[3]) / 100 : parseFloat(components[3]))
        : 1;

    const parsed = {
        red: channel(components[0]),
        green: channel(components[1]),
        blue: channel(components[2]),
        alpha: ohaClamp(Number.isFinite(alpha) ? alpha : 1, 0, 1)
    };

    return Object.values(parsed).every(Number.isFinite) ? parsed : null;
}

function ohaNormalizeCSSColor(value, documentContext = document) {
    const direct = ohaParseRGBColor(value);
    if (direct || !documentContext?.documentElement) {
        return direct;
    }

    const probe = documentContext.createElement('span');
    probe.style.position = 'fixed';
    probe.style.pointerEvents = 'none';
    probe.style.opacity = '0';
    probe.style.color = '';
    probe.style.color = String(value ?? '').trim();
    if (probe.style.color === '') {
        return null;
    }

    documentContext.documentElement.appendChild(probe);
    const normalized = documentContext.defaultView.getComputedStyle(probe).color;
    probe.remove();

    return ohaParseRGBColor(normalized);
}

function ohaMixRGB(first, second, amount) {
    const ratio = ohaClamp(amount, 0, 1);
    return {
        red: first.red + ((second.red - first.red) * ratio),
        green: first.green + ((second.green - first.green) * ratio),
        blue: first.blue + ((second.blue - first.blue) * ratio),
        alpha: 1
    };
}

function ohaRGBToHSL(color) {
    const red = color.red / 255;
    const green = color.green / 255;
    const blue = color.blue / 255;
    const maximum = Math.max(red, green, blue);
    const minimum = Math.min(red, green, blue);
    const delta = maximum - minimum;
    const lightness = (maximum + minimum) / 2;
    let hue = 0;
    let saturation = 0;

    if (delta !== 0) {
        saturation = delta / (1 - Math.abs((2 * lightness) - 1));
        if (maximum === red) {
            hue = 60 * (((green - blue) / delta) % 6);
        } else if (maximum === green) {
            hue = 60 * (((blue - red) / delta) + 2);
        } else {
            hue = 60 * (((red - green) / delta) + 4);
        }
    }

    return {
        hue: hue < 0 ? hue + 360 : hue,
        saturation,
        lightness
    };
}

function ohaHSLToRGB(hue, saturation, lightness) {
    const normalizedHue = ((hue % 360) + 360) % 360;
    const chroma = (1 - Math.abs((2 * lightness) - 1)) * saturation;
    const part = chroma * (1 - Math.abs(((normalizedHue / 60) % 2) - 1));
    const offset = lightness - (chroma / 2);
    let red = 0;
    let green = 0;
    let blue = 0;

    if (normalizedHue < 60) {
        red = chroma;
        green = part;
    } else if (normalizedHue < 120) {
        red = part;
        green = chroma;
    } else if (normalizedHue < 180) {
        green = chroma;
        blue = part;
    } else if (normalizedHue < 240) {
        green = part;
        blue = chroma;
    } else if (normalizedHue < 300) {
        red = part;
        blue = chroma;
    } else {
        red = chroma;
        blue = part;
    }

    return {
        red: (red + offset) * 255,
        green: (green + offset) * 255,
        blue: (blue + offset) * 255,
        alpha: 1
    };
}

function ohaRGBString(color, alpha = 1) {
    const red = Math.round(ohaClamp(color.red, 0, 255));
    const green = Math.round(ohaClamp(color.green, 0, 255));
    const blue = Math.round(ohaClamp(color.blue, 0, 255));
    return alpha >= 0.999
        ? `rgb(${red}, ${green}, ${blue})`
        : `rgba(${red}, ${green}, ${blue}, ${ohaClamp(alpha, 0, 1).toFixed(3)})`;
}

function ohaRelativeLuminance(color) {
    const channel = (value) => {
        const normalized = value / 255;
        return normalized <= 0.03928
            ? normalized / 12.92
            : ((normalized + 0.055) / 1.055) ** 2.4;
    };

    return (0.2126 * channel(color.red))
        + (0.7152 * channel(color.green))
        + (0.0722 * channel(color.blue));
}

function ohaContrastRatio(first, second) {
    const brighter = Math.max(ohaRelativeLuminance(first), ohaRelativeLuminance(second));
    const darker = Math.min(ohaRelativeLuminance(first), ohaRelativeLuminance(second));
    return (brighter + 0.05) / (darker + 0.05);
}

function ohaCompositeRGB(foreground, background, alpha = foreground.alpha ?? 1) {
    const ratio = ohaClamp(alpha, 0, 1);
    return {
        red: (foreground.red * ratio) + (background.red * (1 - ratio)),
        green: (foreground.green * ratio) + (background.green * (1 - ratio)),
        blue: (foreground.blue * ratio) + (background.blue * (1 - ratio)),
        alpha: 1
    };
}

function ohaReadableText(background) {
    const dark = {red: 24, green: 24, blue: 22, alpha: 1};
    const light = {red: 250, green: 250, blue: 248, alpha: 1};
    return ohaContrastRatio(dark, background) >= ohaContrastRatio(light, background) ? dark : light;
}

function ohaEnsureContrast(foreground, background, minimumRatio = 4.5) {
    if (ohaContrastRatio(foreground, background) >= minimumRatio) {
        return foreground;
    }

    const target = ohaReadableText(background);
    for (let step = 1; step <= 20; step += 1) {
        const adjusted = ohaMixRGB(foreground, target, step / 20);
        if (ohaContrastRatio(adjusted, background) >= minimumRatio) {
            return adjusted;
        }
    }

    return target;
}

function ohaSecondaryText(primary, background, mixAmount, minimumRatio) {
    return ohaEnsureContrast(ohaMixRGB(primary, background, mixAmount), background, minimumRatio);
}

function ohaAdaptiveElements() {
    const elements = [];
    const appendChain = (element) => {
        let current = element;
        while (current) {
            if (!elements.includes(current)) {
                elements.push(current);
            }
            current = current.parentElement;
        }
    };

    try {
        if (window.parent !== window && window.frameElement) {
            const frame = window.frameElement;
            const parentDocument = window.parent.document;
            const rectangle = frame.getBoundingClientRect();
            const previousPointerEvents = frame.style.pointerEvents;
            let underlying = null;
            try {
                frame.style.pointerEvents = 'none';
                underlying = parentDocument.elementFromPoint(
                    rectangle.left + (rectangle.width / 2),
                    rectangle.top + (rectangle.height / 2)
                );
            } finally {
                frame.style.pointerEvents = previousPointerEvents;
            }

            appendChain(underlying);
            appendChain(frame.parentElement);
            appendChain(parentDocument.body);
            appendChain(parentDocument.documentElement);
        }
    } catch (error) {
        console.debug('OpenHomeAlarm could not inspect the IPSView host theme.', error);
    }

    return elements;
}

function ohaAdaptiveColorFromStyle(style, properties, documentContext) {
    for (const property of properties) {
        const color = ohaNormalizeCSSColor(style.getPropertyValue(property), documentContext);
        if (color && color.alpha >= 0.12) {
            return color;
        }
    }

    return null;
}

function ohaDetectHostPalette() {
    for (const element of ohaAdaptiveElements()) {
        const documentContext = element.ownerDocument;
        const style = documentContext.defaultView.getComputedStyle(element);
        const base = ohaAdaptiveColorFromStyle(style, ohaAdaptiveBackgroundProperties, documentContext)
            ?? ohaNormalizeCSSColor(style.backgroundColor, documentContext)
            ?? ohaAdaptiveColorFromStyle(style, ohaAdaptiveSurfaceProperties, documentContext);
        const accent = ohaAdaptiveColorFromStyle(style, ohaAdaptiveAccentProperties, documentContext);
        const foreground = ohaNormalizeCSSColor(style.color, documentContext);
        const complexBackground = String(style.backgroundImage ?? '').trim() !== ''
            && String(style.backgroundImage).trim() !== 'none';

        if (base && base.alpha >= 0.72) {
            return {base, accent, foreground, detected: true, complexBackground};
        }
    }

    const transparent = document.documentElement.classList.contains('oha-transparent')
        || ohaIPSViewConfig?.transparent === true;
    const fallbackBase = transparent
        ? {red: 218, green: 202, blue: 160, alpha: 1}
        : (ohaAdaptiveThemeMedia.matches
            ? {red: 36, green: 41, blue: 50, alpha: 1}
            : {red: 222, green: 226, blue: 232, alpha: 1});

    return {
        base: fallbackBase,
        accent: null,
        foreground: null,
        detected: false,
        complexBackground: transparent
    };
}

function ohaDerivedAdaptiveAccent(base, detectedAccent, lightEnvironment, panelBackground) {
    let accent = detectedAccent && detectedAccent.alpha >= 0.35
        ? detectedAccent
        : null;

    if (!accent) {
        const hsl = ohaRGBToHSL(base);
        if (hsl.saturation < 0.12) {
            accent = lightEnvironment
                ? {red: 72, green: 91, blue: 124, alpha: 1}
                : {red: 160, green: 183, blue: 224, alpha: 1};
        } else {
            accent = ohaHSLToRGB(
                hsl.hue,
                ohaClamp(Math.max(0.44, hsl.saturation * 1.22), 0.44, 0.76),
                lightEnvironment ? 0.34 : 0.72
            );
        }
    }

    return ohaEnsureContrast(accent, panelBackground, 4.5);
}

function ohaApplyAdaptiveTheme() {
    const root = document.documentElement;
    if (!root.classList.contains('oha-theme-adaptive')) {
        return;
    }

    const palette = ohaDetectHostPalette();
    const base = palette.base;
    const baseLuminance = ohaRelativeLuminance(base);
    const transparent = root.classList.contains('oha-transparent') || ohaIPSViewConfig?.transparent === true;
    const lightThreshold = transparent && palette.complexBackground ? 0.22 : 0.36;
    let lightEnvironment = baseLuminance >= lightThreshold;

    // Host text colors are only a weak hint. IPSView themes frequently use white
    // labels over bright or textured backgrounds, so they must never override a
    // clearly light background color.
    if (
        palette.foreground
        && Math.abs(baseLuminance - lightThreshold) < 0.045
    ) {
        lightEnvironment = ohaRelativeLuminance(palette.foreground) < 0.34;
    }

    const white = {red: 255, green: 255, blue: 255, alpha: 1};
    const black = {red: 12, green: 14, blue: 18, alpha: 1};

    const surface = lightEnvironment
        ? ohaMixRGB(base, white, palette.complexBackground ? 0.70 : 0.58)
        : ohaMixRGB(base, black, palette.complexBackground ? 0.58 : 0.46);
    const surfaceStrong = lightEnvironment
        ? ohaMixRGB(base, white, palette.complexBackground ? 0.82 : 0.72)
        : ohaMixRGB(base, black, palette.complexBackground ? 0.68 : 0.58);
    const surfaceSoft = lightEnvironment
        ? ohaMixRGB(base, white, palette.complexBackground ? 0.52 : 0.42)
        : ohaMixRGB(base, black, palette.complexBackground ? 0.42 : 0.30);

    const surfaceAlpha = palette.complexBackground ? 0.93 : 0.88;
    const strongAlpha = palette.complexBackground ? 0.97 : 0.94;
    const softAlpha = palette.complexBackground ? 0.88 : 0.80;
    const renderedSurface = ohaCompositeRGB(surface, base, surfaceAlpha);
    const renderedStrong = ohaCompositeRGB(surfaceStrong, base, strongAlpha);
    const renderedSoft = ohaCompositeRGB(surfaceSoft, base, softAlpha);

    const panelText = ohaReadableText(renderedSurface);
    const panelMuted = ohaSecondaryText(panelText, renderedSurface, 0.34, 4.5);
    const panelFaint = ohaSecondaryText(panelText, renderedSurface, 0.52, 3.1);
    const pageText = ohaReadableText(base);
    const pageMuted = ohaSecondaryText(pageText, base, 0.26, 4.5);
    const pageFaint = ohaSecondaryText(pageText, base, 0.42, 3.1);
    const accent = ohaDerivedAdaptiveAccent(base, palette.accent, lightEnvironment, renderedStrong);

    const success = ohaEnsureContrast(
        lightEnvironment
            ? {red: 25, green: 112, blue: 66, alpha: 1}
            : {red: 106, green: 225, blue: 153, alpha: 1},
        renderedSurface,
        4.5
    );
    const warning = ohaEnsureContrast(
        lightEnvironment
            ? {red: 139, green: 86, blue: 0, alpha: 1}
            : {red: 255, green: 199, blue: 91, alpha: 1},
        renderedSurface,
        4.5
    );
    const danger = ohaEnsureContrast(
        lightEnvironment
            ? {red: 177, green: 46, blue: 46, alpha: 1}
            : {red: 255, green: 132, blue: 132, alpha: 1},
        renderedSurface,
        4.5
    );

    root.dataset.environmentTone = lightEnvironment ? 'light' : 'dark';
    root.dataset.paletteSource = palette.detected ? 'host' : 'safe-fallback';
    root.style.setProperty('color-scheme', lightEnvironment ? 'light' : 'dark');
    root.style.setProperty('--oha-environment-color', ohaRGBString(base));
    root.style.setProperty('--oha-bg', transparent ? 'transparent' : ohaRGBString(base, 0.97));
    root.style.setProperty('--oha-surface', ohaRGBString(surface, surfaceAlpha));
    root.style.setProperty('--oha-surface-strong', ohaRGBString(surfaceStrong, strongAlpha));
    root.style.setProperty('--oha-surface-soft', ohaRGBString(surfaceSoft, softAlpha));
    root.style.setProperty('--oha-border', ohaRGBString(panelText, lightEnvironment ? 0.18 : 0.20));
    root.style.setProperty('--oha-text', ohaRGBString(pageText));
    root.style.setProperty('--oha-muted', ohaRGBString(pageMuted));
    root.style.setProperty('--oha-faint', ohaRGBString(pageFaint));
    root.style.setProperty('--oha-panel-text', ohaRGBString(panelText));
    root.style.setProperty('--oha-panel-muted', ohaRGBString(panelMuted));
    root.style.setProperty('--oha-panel-faint', ohaRGBString(panelFaint));
    root.style.setProperty('--oha-panel-border', ohaRGBString(panelText, lightEnvironment ? 0.18 : 0.20));
    root.style.setProperty('--oha-page-label-bg', ohaRGBString(surfaceStrong, strongAlpha));
    root.style.setProperty('--oha-page-label-text', ohaRGBString(ohaReadableText(renderedStrong)));
    root.style.setProperty('--oha-page-label-border', ohaRGBString(ohaReadableText(renderedStrong), 0.16));
    root.style.setProperty('--oha-accent', ohaRGBString(accent));
    root.style.setProperty('--oha-accent-soft', ohaRGBString(accent, lightEnvironment ? 0.13 : 0.18));
    root.style.setProperty('--oha-success', ohaRGBString(success));
    root.style.setProperty('--oha-success-soft', ohaRGBString(success, lightEnvironment ? 0.12 : 0.17));
    root.style.setProperty('--oha-success-border', ohaRGBString(success, 0.30));
    root.style.setProperty('--oha-warning', ohaRGBString(warning));
    root.style.setProperty('--oha-warning-soft', ohaRGBString(warning, lightEnvironment ? 0.12 : 0.17));
    root.style.setProperty('--oha-danger', ohaRGBString(danger));
    root.style.setProperty('--oha-danger-soft', ohaRGBString(danger, lightEnvironment ? 0.12 : 0.17));
    root.style.setProperty('--oha-shadow', lightEnvironment
        ? '0 10px 24px rgba(63, 48, 27, 0.13)'
        : '0 12px 30px rgba(0, 0, 0, 0.22)');
    root.style.setProperty('--oha-adaptive-backdrop', palette.complexBackground
        ? 'blur(14px) saturate(0.90)'
        : 'blur(8px) saturate(1.04)');

    // Keep the computed variables internally consistent even when a browser
    // renders transparent layers over a textured IPSView background.
    root.style.setProperty('--oha-rendered-surface', ohaRGBString(renderedSurface));
    root.style.setProperty('--oha-rendered-surface-strong', ohaRGBString(renderedStrong));
    root.style.setProperty('--oha-rendered-surface-soft', ohaRGBString(renderedSoft));
}

function ohaScheduleAdaptiveTheme(delay = 0) {
    if (!document.documentElement.classList.contains('oha-theme-adaptive')) {
        return;
    }

    if (ohaAdaptiveThemeTimer !== null) {
        window.clearTimeout(ohaAdaptiveThemeTimer);
    }
    ohaAdaptiveThemeTimer = window.setTimeout(() => {
        ohaAdaptiveThemeTimer = null;
        ohaApplyAdaptiveTheme();
    }, delay);
}

function ohaInitializeAdaptiveTheme() {
    if (!document.documentElement.classList.contains('oha-theme-adaptive')) {
        return;
    }

    ohaApplyAdaptiveTheme();
    window.addEventListener('resize', () => ohaScheduleAdaptiveTheme(120));
    window.addEventListener('focus', () => ohaScheduleAdaptiveTheme(80));
    window.addEventListener('pageshow', () => ohaScheduleAdaptiveTheme(80));

    if (typeof ohaAdaptiveThemeMedia.addEventListener === 'function') {
        ohaAdaptiveThemeMedia.addEventListener('change', () => ohaScheduleAdaptiveTheme(0));
    }

    try {
        if (window.parent !== window) {
            ohaAdaptiveThemeObserver = new MutationObserver(() => ohaScheduleAdaptiveTheme(120));
            const parentDocument = window.parent.document;
            ohaAdaptiveThemeObserver.observe(parentDocument.documentElement, {
                attributes: true,
                attributeFilter: ['class', 'style']
            });
            if (parentDocument.body) {
                ohaAdaptiveThemeObserver.observe(parentDocument.body, {
                    attributes: true,
                    attributeFilter: ['class', 'style']
                });
            }
        }
    } catch (error) {
        console.debug('OpenHomeAlarm could not observe the IPSView host theme.', error);
    }
}

function ohaTranslate(text) {
    if (typeof translate === 'function') {
        return translate(text);
    }

    const translated = window.OHA_TRANSLATIONS?.[text];
    return typeof translated === 'string' ? translated : text;
}

function ohaStateCaption(name) {
    const captions = {
        disarmed: 'Disarmed',
        exit_delay: 'Exit delay',
        armed: 'Armed',
        entry_delay: 'Entry delay',
        alarm: 'Alarm'
    };

    return ohaTranslate(captions[name] ?? 'Unknown state');
}

function ohaModeCaption(name) {
    const captions = {
        none: 'No arming mode',
        home: 'Home',
        away: 'Away',
        night: 'Night'
    };

    return ohaTranslate(captions[name] ?? 'Unknown mode');
}

function ohaReasonCaption(reason) {
    const captions = {
        triggered: 'Triggered',
        unavailable: 'Unavailable',
        active: 'Active'
    };

    return ohaTranslate(captions[reason] ?? reason);
}

function ohaEventCaption(eventName) {
    const captions = {
        arm_rejected: 'Arming rejected',
        arm_cancelled: 'Arming cancelled',
        exit_delay_started: 'Exit delay started',
        armed: 'System armed',
        entry_delay_started: 'Entry delay started',
        alarm: 'Alarm triggered',
        alarm_output_reset: 'Alarm output reset',
        disarmed: 'System disarmed',
        disarm_code_rejected: 'Disarm code rejected',
        disarm_code_locked: 'Code entry locked',
        sensor_bypassed: 'Sensor bypassed',
        sensor_bypass_removed: 'Sensor bypass restored',
        sensor_bypasses_cleared: 'All bypasses cleared',
        alarm_memory_cleared: 'Alarm memory acknowledged',
        fault_activated: 'System fault detected',
        fault_cleared: 'System fault cleared'
    };

    return ohaTranslate(captions[eventName] ?? eventName);
}

function ohaFormatEventTime(timestamp) {
    const numericTimestamp = Number(timestamp);
    if (!Number.isFinite(numericTimestamp) || numericTimestamp <= 0) {
        return '';
    }

    return new Intl.DateTimeFormat(undefined, {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    }).format(new Date(numericTimestamp * 1000));
}

function ohaStateIcon(name) {
    const icons = {
        disarmed: 'fa-shield',
        exit_delay: 'fa-timer',
        armed: 'fa-lock',
        entry_delay: 'fa-key',
        alarm: 'fa-bell'
    };

    return icons[name] ?? 'fa-shield';
}

function ohaAllModesReady(state) {
    return Boolean(state.Modes?.home?.Ready && state.Modes?.away?.Ready && state.Modes?.night?.Ready);
}

function ohaAnyModeReady(state) {
    return Boolean(state.Modes?.home?.Ready || state.Modes?.away?.Ready || state.Modes?.night?.Ready);
}

function ohaHeroMessage(state) {
    switch (state.State.Name) {
        case 'disarmed':
            if (state.Faults?.Active) {
                return ohaTranslate('Check active system faults before arming.');
            }
            if (ohaAllModesReady(state)) {
                return ohaTranslate('All arming modes are ready.');
            }
            if (ohaAnyModeReady(state)) {
                return ohaTranslate('Choose one of the available arming modes.');
            }
            return ohaTranslate('Resolve the blocking sensors before arming.');

        case 'exit_delay':
            return ohaTranslate('Leave the secured area before the countdown ends.');

        case 'armed':
            return ohaTranslate('Monitoring is active for the selected arming mode.');

        case 'entry_delay':
            return state.Delay?.Source
                ? `${ohaTranslate('Source')}: ${state.Delay.Source}`
                : ohaTranslate('Disarm the system before the countdown ends.');

        case 'alarm':
            if (state.Alarm?.LastSource) {
                return `${ohaTranslate('Source')}: ${state.Alarm.LastSource}`;
            }
            return ohaTranslate('An alarm has been triggered.');

        default:
            return '';
    }
}

function ohaHeroEyebrow(state) {
    if (state.State.Name === 'alarm') {
        return ohaTranslate('Security alarm');
    }
    if (state.State.Name === 'entry_delay' || state.State.Name === 'exit_delay') {
        return ohaTranslate('Countdown');
    }
    return ohaTranslate('Security status');
}

function ohaRenderHero(state) {
    const root = document.getElementById('ohaRoot');
    const stateName = state.State.Name;
    const countdownActive = stateName === 'exit_delay' || stateName === 'entry_delay';

    root.dataset.state = stateName;
    document.getElementById('heroEyebrow').textContent = ohaHeroEyebrow(state);
    document.getElementById('stateLabel').textContent = ohaStateCaption(stateName);
    document.getElementById('heroMessage').textContent = ohaHeroMessage(state);
    document.getElementById('modeLabel').textContent = ohaModeCaption(state.Mode.Name);
    const stateIcon = document.querySelector('#stateIcon i');
    if (stateIcon) {
        stateIcon.className = `fa-light ${ohaStateIcon(stateName)}`;
    }

    const countdown = document.getElementById('heroCountdown');
    countdown.hidden = !countdownActive;
    if (countdownActive) {
        document.getElementById('delayRemaining').textContent = String(Math.max(0, Number(state.Delay?.Remaining) || 0));
        document.getElementById('countdownUnit').textContent = ohaTranslate('Seconds');
    }
}

function ohaRenderMode(modeName, modeState) {
    const button = document.querySelector(`.oha-mode-button[data-mode="${modeName}"]`);
    if (!button) {
        return;
    }

    button.dataset.ready = modeState.Ready ? 'true' : 'false';
    button.dataset.canArm = modeState.CanArm ? 'true' : 'false';
    button.setAttribute('aria-disabled', modeState.CanArm ? 'false' : 'true');

    const title = button.querySelector('[data-role="mode-title"]');
    if (title) {
        title.textContent = ohaModeCaption(modeName);
    }

    const readiness = button.querySelector('[data-role="readiness"]');
    if (readiness) {
        readiness.textContent = ohaTranslate(modeState.Ready ? 'Ready' : 'Not ready');
    }

    const blockers = button.querySelector('[data-role="blockers"]');
    if (blockers) {
        blockers.replaceChildren();
        const items = Array.isArray(modeState.Blockers) ? modeState.Blockers : [];
        blockers.dataset.empty = items.length === 0 ? 'true' : 'false';

        if (items.length === 0) {
            blockers.textContent = ohaTranslate('No blocking sensors');
        } else {
            for (const blocker of items.slice(0, 2)) {
                const line = document.createElement('span');
                line.className = 'oha-blocker';
                line.textContent = `${blocker.Name} · ${ohaReasonCaption(blocker.Reason)}`;
                blockers.appendChild(line);
            }

            if (items.length > 2) {
                const more = document.createElement('span');
                more.className = 'oha-blocker';
                more.textContent = `+${items.length - 2} ${ohaTranslate('more')}`;
                blockers.appendChild(more);
            }
        }
    }

    const action = button.querySelector('[data-role="mode-action"]');
    if (action) {
        if (modeState.CanArm) {
            action.textContent = ohaTranslate('Activate');
        } else if (button.dataset.active === 'true') {
            action.textContent = ohaTranslate('Active');
        } else if (ohaState?.State?.Name === 'disarmed') {
            action.textContent = ohaTranslate('Blocked');
        } else {
            action.textContent = ohaTranslate('Deactivate first to change mode');
        }
    }
}

function ohaRenderArming(state) {
    const section = document.getElementById('armingSection');
    const isDisarmed = state.State.Name === 'disarmed';
    const activeMode = state.Mode?.Name ?? 'none';

    section.hidden = false;
    document.getElementById('armingKicker').textContent = ohaTranslate('Arming modes');
    document.getElementById('armingTitle').textContent = ohaTranslate(isDisarmed ? 'Select security mode' : 'Security zones');
    document.getElementById('armingHint').textContent = isDisarmed
        ? ohaTranslate('Select a ready mode to arm')
        : `${ohaTranslate('Active mode')}: ${ohaModeCaption(activeMode)}`;

    for (const modeName of ['home', 'away', 'night']) {
        const button = document.querySelector(`.oha-mode-button[data-mode="${modeName}"]`);
        if (button) {
            button.dataset.active = !isDisarmed && activeMode === modeName ? 'true' : 'false';
            button.setAttribute('aria-pressed', button.dataset.active);
        }
        ohaRenderMode(modeName, state.Modes[modeName]);
    }
}

function ohaRenderSummary(state) {
    const activeMode = state.Mode?.Name ?? 'none';
    const activeModeState = state.Modes?.[activeMode] ?? null;
    const monitoringActive = state.State?.Name !== 'disarmed';

    document.getElementById('summaryModeLabel').textContent = ohaTranslate('Arming mode');
    document.getElementById('summaryModeValue').textContent = ohaModeCaption(activeMode);

    document.getElementById('summaryMonitoringLabel').textContent = ohaTranslate('Monitoring');
    document.getElementById('summaryMonitoringValue').textContent = ohaTranslate(
        monitoringActive ? 'Active' : (ohaAnyModeReady(state) ? 'Ready' : 'Not ready')
    );

    document.getElementById('summaryFaultLabel').textContent = ohaTranslate('System status');
    document.getElementById('summaryFaultValue').textContent = ohaTranslate(
        state.Faults?.Active ? 'Fault active' : 'No fault'
    );

    document.getElementById('summaryMemoryLabel').textContent = ohaTranslate('Alarm memory');
    document.getElementById('summaryMemoryValue').textContent = ohaTranslate(
        state.Alarm?.MemoryActive ? 'Stored' : 'Empty'
    );

    const monitoringCard = document.getElementById('summaryMonitoringValue').closest('.oha-status-card');
    if (monitoringCard) {
        monitoringCard.dataset.tone = monitoringActive || activeModeState?.Ready ? 'success' : 'warning';
    }

    const faultCard = document.getElementById('summaryFaultValue').closest('.oha-status-card');
    if (faultCard) {
        faultCard.dataset.tone = state.Faults?.Active ? 'warning' : 'success';
    }

    const memoryCard = document.getElementById('summaryMemoryValue').closest('.oha-status-card');
    if (memoryCard) {
        memoryCard.dataset.tone = state.Alarm?.MemoryActive ? 'warning' : 'neutral';
    }
}

function ohaRenderAlarmMemory(state) {
    const panel = document.getElementById('alarmMemoryPanel');
    const memoryActive = Boolean(state.Alarm?.MemoryActive);
    const alarmActive = state.State.Name === 'alarm';

    panel.hidden = !memoryActive || alarmActive;
    if (panel.hidden) {
        return;
    }

    document.getElementById('alarmMemoryTitle').textContent = ohaTranslate('Alarm memory');
    const parts = [];
    if (state.Alarm?.LastSource) {
        parts.push(state.Alarm.LastSource);
    }
    if (state.Alarm?.LastTime) {
        parts.push(state.Alarm.LastTime);
    }
    document.getElementById('alarmMemoryDetail').textContent = parts.join(' · ') || ohaTranslate('Alarm stored');

    const clearButton = document.getElementById('clearAlarmMemoryButton');
    const canClear = Boolean(state.Capabilities?.CanClearAlarmMemory);
    clearButton.hidden = !canClear;
    clearButton.dataset.enabled = canClear ? 'true' : 'false';
    document.getElementById('clearAlarmMemoryLabel').textContent = ohaTranslate('Acknowledge');
}

function ohaRenderFaults(state) {
    const panel = document.getElementById('faultPanel');
    const faults = Array.isArray(state.Faults?.Items) ? state.Faults.Items : [];

    panel.hidden = !state.Faults?.Active;
    if (panel.hidden) {
        return;
    }

    document.getElementById('faultTitle').textContent = ohaTranslate('System fault');
    document.getElementById('faultDetail').textContent = faults.length > 0
        ? faults.map((fault) => `${fault.Name} · ${ohaReasonCaption(fault.Reason)}`).join(', ')
        : ohaTranslate('System fault active');
}

function ohaRenderBypasses(state) {
    const panel = document.getElementById('bypassPanel');
    const bypassed = Array.isArray(state.BypassedSensors) ? state.BypassedSensors : [];

    panel.hidden = bypassed.length === 0;
    if (panel.hidden) {
        return;
    }

    document.getElementById('bypassTitle').textContent = ohaTranslate('Bypassed sensors');
    document.getElementById('bypassDetail').textContent = bypassed.map((sensor) => sensor.Name).join(', ');

    const clearButton = document.getElementById('clearBypassesButton');
    const canClear = Boolean(state.Capabilities?.CanManageBypasses);
    clearButton.hidden = !canClear;
    clearButton.dataset.enabled = canClear ? 'true' : 'false';
    document.getElementById('clearBypassesLabel').textContent = ohaTranslate('Restore all');
}

function ohaCollectSensorOperations(state) {
    const operations = new Map();
    for (const modeName of ['home', 'away', 'night']) {
        const blockers = Array.isArray(state.Modes?.[modeName]?.Blockers)
            ? state.Modes[modeName].Blockers
            : [];

        for (const blocker of blockers) {
            const key = `${blocker.Kind ?? 'unknown'}:${Number(blocker.VariableID) || 0}`;
            const existing = operations.get(key);
            if (existing) {
                if (!existing.Modes.includes(modeName)) {
                    existing.Modes.push(modeName);
                }
                existing.Bypassable = existing.Bypassable || Boolean(blocker.Bypassable);
                continue;
            }

            operations.set(key, {
                Kind: blocker.Kind ?? 'sensor',
                VariableID: Number(blocker.VariableID) || 0,
                Name: blocker.Name ?? ohaTranslate('Unknown sensor'),
                Reason: blocker.Reason ?? 'active',
                Bypassable: Boolean(blocker.Bypassable),
                Modes: [modeName],
                Bypassed: false
            });
        }
    }

    const bypassed = Array.isArray(state.BypassedSensors) ? state.BypassedSensors : [];
    for (const sensor of bypassed) {
        operations.set(`bypassed:${Number(sensor.VariableID) || 0}`, {
            Kind: 'sensor',
            VariableID: Number(sensor.VariableID) || 0,
            Name: sensor.Name ?? ohaTranslate('Unknown sensor'),
            Reason: 'bypassed',
            Bypassable: false,
            Modes: [],
            Bypassed: true
        });
    }

    return Array.from(operations.values());
}

function ohaCreateOperationButton(action, variableID, caption, tone = 'neutral') {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'oha-row-action';
    button.dataset.operation = action;
    button.dataset.variableId = String(variableID);
    button.dataset.tone = tone;
    button.dataset.enabled = 'true';
    button.textContent = ohaTranslate(caption);

    return button;
}

function ohaRenderSensorManagement(state) {
    const panel = document.getElementById('sensorManagementPanel');
    const list = document.getElementById('sensorManagementList');
    const operations = ohaCollectSensorOperations(state);

    panel.hidden = operations.length === 0;
    list.replaceChildren();
    if (panel.hidden) {
        return;
    }

    document.getElementById('sensorManagementKicker').textContent = ohaTranslate('Security zones');
    document.getElementById('sensorManagementTitle').textContent = ohaTranslate('Sensor management');
    document.getElementById('sensorManagementCount').textContent = String(operations.length);

    for (const operation of operations) {
        const row = document.createElement('div');
        row.className = 'oha-operation-row';
        row.dataset.tone = operation.Bypassed ? 'bypass' : (operation.Kind === 'fault' ? 'warning' : 'danger');

        const icon = document.createElement('span');
        icon.className = 'oha-operation-icon';
        icon.setAttribute('aria-hidden', 'true');
        const iconElement = document.createElement('i');
        iconElement.className = `fa-light ${operation.Bypassed
            ? 'fa-eye-slash'
            : (operation.Kind === 'fault' ? 'fa-triangle-exclamation' : 'fa-door-open')}`;
        icon.appendChild(iconElement);

        const copy = document.createElement('div');
        copy.className = 'oha-operation-copy';
        const title = document.createElement('strong');
        title.textContent = operation.Name;
        const detail = document.createElement('span');
        if (operation.Bypassed) {
            detail.textContent = ohaTranslate('Temporarily bypassed');
        } else {
            const modeLabels = operation.Modes.map((mode) => ohaModeCaption(mode)).join(', ');
            detail.textContent = `${ohaReasonCaption(operation.Reason)}${modeLabels ? ` · ${modeLabels}` : ''}`;
        }
        copy.append(title, detail);

        row.append(icon, copy);
        if (operation.Bypassed && state.Capabilities?.CanManageBypasses) {
            row.append(ohaCreateOperationButton(
                'RemoveSensorBypass',
                operation.VariableID,
                'Restore',
                'accent'
            ));
        } else if (
            operation.Kind === 'sensor'
            && operation.Bypassable
            && state.Capabilities?.CanManageBypasses
        ) {
            row.append(ohaCreateOperationButton(
                'BypassSensor',
                operation.VariableID,
                'Bypass once',
                'warning'
            ));
        }

        list.appendChild(row);
    }
}

function ohaRenderEventHistory(state) {
    const panel = document.getElementById('eventHistoryPanel');
    const list = document.getElementById('eventHistoryList');
    const events = Array.isArray(state.RecentEvents) ? state.RecentEvents : [];

    panel.hidden = events.length === 0;
    list.replaceChildren();
    if (panel.hidden) {
        return;
    }

    document.getElementById('eventHistoryKicker').textContent = ohaTranslate('System log');
    document.getElementById('eventHistoryTitle').textContent = ohaTranslate('Recent activity');

    for (const event of events) {
        const row = document.createElement('div');
        row.className = 'oha-history-row';

        const marker = document.createElement('span');
        marker.className = 'oha-history-marker';
        marker.setAttribute('aria-hidden', 'true');

        const copy = document.createElement('div');
        copy.className = 'oha-history-copy';
        const title = document.createElement('strong');
        title.textContent = ohaEventCaption(event.Event);
        const detail = document.createElement('span');
        detail.textContent = [event.Source, ohaFormatEventTime(event.Time)].filter(Boolean).join(' · ');
        copy.append(title, detail);
        row.append(marker, copy);
        list.appendChild(row);
    }
}

function ohaUpdateOperationsLayout() {
    const grid = document.getElementById('operationsGrid');
    const visiblePanels = Array.from(grid.querySelectorAll('.oha-operation-panel'))
        .filter((panel) => !panel.hidden)
        .length;

    grid.hidden = visiblePanels === 0;
    grid.dataset.visiblePanels = String(visiblePanels);
}

function ohaCanDisarm(state = ohaState) {
    const stateName = state?.State?.Name ?? 'disarmed';
    const modeName = state?.Mode?.Name ?? 'none';

    return stateName !== 'disarmed' || modeName !== 'none';
}

function ohaCodeProtectionLocked(state = ohaState) {
    return Boolean(state?.CodeProtection?.Locked);
}

function ohaCodeInputAllowed() {
    return Boolean(
        ohaState?.Capabilities?.CodeRequired
        && !ohaCodeProtectionLocked(ohaState)
        && ohaCanDisarm(ohaState)
    );
}

function ohaRenderInlineCodepad(state) {
    const panel = document.getElementById('inlineCodepad');
    const locked = ohaCodeProtectionLocked(state);
    const enabled = Boolean(state.Capabilities?.CodeRequired && !locked && ohaCanDisarm(state));
    const codeRequired = Boolean(state.Capabilities?.CodeRequired);

    panel.hidden = !codeRequired || !ohaCanDisarm(state);
    document.getElementById('ohaRoot').dataset.codepadVisible = panel.hidden ? 'false' : 'true';
    panel.dataset.enabled = enabled ? 'true' : 'false';
    document.getElementById('inlineCodepadKicker').textContent = ohaTranslate('Security control');
    document.getElementById('inlineCodepadTitle').textContent = ohaTranslate('Code pad');
    document.getElementById('inlineCodepadState').textContent = ohaTranslate(
        locked ? 'Blocked' : (enabled ? 'Active' : 'Inactive')
    );

    if (locked) {
        document.getElementById('inlineCodepadHint').textContent = ohaTranslate('Code entry is temporarily locked.');
    } else if (enabled) {
        document.getElementById('inlineCodepadHint').textContent = ohaTranslate('Enter the 4 to 8 digit disarm code.');
    } else if (codeRequired) {
        document.getElementById('inlineCodepadHint').textContent = ohaTranslate('Code entry becomes available when disarming is possible.');
    } else {
        document.getElementById('inlineCodepadHint').textContent = ohaTranslate('Code protection is not enabled.');
    }

    document.getElementById('inlineDisarmLabel').textContent = ohaTranslate('Deactivate');
    ohaUpdateCodepad();
}

function ohaRenderDisarm(state) {
    const bar = document.getElementById('controlBar');
    const button = document.getElementById('disarmButton');
    const label = document.getElementById('disarmButtonLabel');
    const codeHint = document.getElementById('codeHint');
    const codeRequired = Boolean(state.Capabilities?.CodeRequired);
    const codeLocked = ohaCodeProtectionLocked(state);
    const canDisarm = ohaCanDisarm(state);
    const resetAlarmOutputButton = document.getElementById('resetAlarmOutputButton');
    const canResetAlarmOutput = Boolean(state.Capabilities?.CanResetAlarmOutput);

    bar.hidden = !canDisarm;
    bar.dataset.codeRequired = codeRequired ? 'true' : 'false';

    if (!canDisarm) {
        ohaCloseCodepad();
        return;
    }

    document.getElementById('controlTitle').textContent = ohaTranslate('System control');
    resetAlarmOutputButton.hidden = !canResetAlarmOutput;
    resetAlarmOutputButton.dataset.enabled = canResetAlarmOutput ? 'true' : 'false';
    document.getElementById('resetAlarmOutputLabel').textContent = ohaTranslate('Silence alarm');
    codeHint.hidden = !codeRequired;
    codeHint.textContent = codeRequired
        ? ohaTranslate(codeLocked ? 'Code entry is temporarily locked.' : 'Code required for disarming')
        : '';
    button.dataset.enabled = codeLocked ? 'false' : 'true';
    button.setAttribute('aria-disabled', codeLocked ? 'true' : 'false');
    button.dataset.codeRequired = codeRequired ? 'true' : 'false';
    label.textContent = ohaTranslate('Deactivate');
}

function ohaRenderStaticText() {
    document.getElementById('refreshButton').setAttribute('aria-label', ohaTranslate('Refresh'));
    document.getElementById('statusGrid').setAttribute('aria-label', ohaTranslate('System overview'));
    document.getElementById('codepadTitle').textContent = ohaTranslate('Disarm system');
    document.getElementById('codepadHint').textContent = ohaTranslate('Enter the 4 to 8 digit disarm code.');
    document.getElementById('codepadClose').setAttribute('aria-label', ohaTranslate('Cancel code entry'));
    document.getElementById('codepadDelete').setAttribute('aria-label', ohaTranslate('Delete last digit'));
    document.getElementById('codepadClear').setAttribute('aria-label', ohaTranslate('Clear code entry'));
    document.getElementById('codepadConfirm').setAttribute('aria-label', ohaTranslate('Deactivate'));
    document.getElementById('modalDisarmLabel').textContent = ohaTranslate('Deactivate');
    document.getElementById('codepadGrid').setAttribute('aria-label', ohaTranslate('Code pad'));
    document.getElementById('inlineCodepad').setAttribute('aria-label', ohaTranslate('Code pad'));
    document.getElementById('inlineCodepadDelete').setAttribute('aria-label', ohaTranslate('Delete last digit'));
    document.getElementById('inlineCodepadClear').setAttribute('aria-label', ohaTranslate('Clear code entry'));
    document.getElementById('inlineCodepadConfirm').setAttribute('aria-label', ohaTranslate('Deactivate'));
    document.getElementById('inlineCodepadGrid').setAttribute('aria-label', ohaTranslate('Code pad'));
}

function ohaRender() {
    if (!ohaState || Number(ohaState.ApiVersion) !== 1) {
        return;
    }

    ohaRenderStaticText();
    ohaRenderHero(ohaState);
    ohaRenderSummary(ohaState);
    ohaRenderAlarmMemory(ohaState);
    ohaRenderFaults(ohaState);
    ohaRenderBypasses(ohaState);
    ohaRenderSensorManagement(ohaState);
    ohaRenderEventHistory(ohaState);
    ohaUpdateOperationsLayout();
    ohaRenderArming(ohaState);
    ohaRenderInlineCodepad(ohaState);
    ohaRenderDisarm(ohaState);
    ohaScheduleCodeLockRefresh(ohaState);
}

async function ohaIPSViewRequest(action, value) {
    if (!ohaIPSViewConfig?.endpoint || !ohaIPSViewConfig?.token) {
        throw new Error('IPSView transport is not configured.');
    }

    const body = new URLSearchParams();
    body.set('token', String(ohaIPSViewConfig.token));
    body.set('action', action);
    body.set('value', JSON.stringify(value));

    ohaIPSViewPendingRequests += 1;
    try {
        const response = await fetch(String(ohaIPSViewConfig.endpoint), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
            },
            body: body.toString(),
            cache: 'no-store',
            credentials: 'same-origin'
        });
        const payload = await response.json();
        if (!response.ok || payload?.Error) {
            throw new Error(payload?.Error || `HTTP ${response.status}`);
        }

        handleMessage(payload);
        return payload;
    } finally {
        ohaIPSViewPendingRequests = Math.max(0, ohaIPSViewPendingRequests - 1);
    }
}

function ohaHandleTransportError(ident, error) {
    console.error('OpenHomeAlarm IPSView request failed:', error);
    if (ident !== 'DisarmWithCode') {
        return;
    }

    ohaClearCodeRequestTimer();
    ohaCodeBusy = false;
    ohaSetCodeError(ohaTranslate('No response from the alarm system. Please try again.'));
    ohaUpdateCodepad();
}

function ohaRequestAction(ident, value) {
    if (typeof requestAction === 'function') {
        requestAction(ident, value);
        return;
    }

    if (ohaIPSViewConfig) {
        void ohaIPSViewRequest(ident === 'RefreshVisualization' ? 'GetState' : ident, value)
            .catch((error) => ohaHandleTransportError(ident, error));
    }
}

function ohaIPSViewPollInterval() {
    if (document.visibilityState === 'hidden') {
        return Math.max(5000, Number(ohaIPSViewConfig?.hiddenPollInterval) || 15000);
    }

    const stateName = ohaState?.State?.Name ?? '';
    if (stateName === 'exit_delay' || stateName === 'entry_delay' || ohaCodeProtectionLocked(ohaState)) {
        return Math.max(500, Number(ohaIPSViewConfig?.activePollInterval) || 1000);
    }

    return Math.max(1000, Number(ohaIPSViewConfig?.pollInterval) || 3000);
}

function ohaScheduleIPSViewPoll(delay = null) {
    if (!ohaIPSViewConfig) {
        return;
    }

    if (ohaIPSViewPollTimer !== null) {
        window.clearTimeout(ohaIPSViewPollTimer);
    }
    ohaIPSViewPollTimer = window.setTimeout(ohaPollIPSViewState, delay ?? ohaIPSViewPollInterval());
}

async function ohaPollIPSViewState() {
    ohaIPSViewPollTimer = null;
    if (ohaIPSViewPendingRequests > 0) {
        ohaScheduleIPSViewPoll(500);
        return;
    }

    try {
        await ohaIPSViewRequest('GetState', null);
    } catch (error) {
        console.error('OpenHomeAlarm IPSView refresh failed:', error);
    } finally {
        ohaScheduleIPSViewPoll();
    }
}

function ohaHandleModeButton(button) {
    if (!(button instanceof HTMLElement)) {
        return;
    }

    if (button.dataset.canArm === 'true') {
        ohaRequestAction('Arm', button.dataset.mode ?? '');
        return;
    }

    if (ohaState?.State?.Name !== 'disarmed') {
        const hint = document.getElementById('armingHint');
        if (hint) {
            hint.textContent = ohaTranslate('Deactivate first to change mode');
        }
    }
}

function ohaInlineCodepadVisible() {
    const inlineCodepad = document.getElementById('inlineCodepad');
    return inlineCodepad !== null && window.getComputedStyle(inlineCodepad).display !== 'none';
}

function ohaFocusInlineCodepad() {
    if (!ohaCodeInputAllowed() || !ohaInlineCodepadVisible()) {
        return false;
    }

    document.querySelector('#inlineCodepad [data-code-digit="1"]')?.focus();
    return true;
}

function ohaHandleDisarmButton() {
    if (!ohaCanDisarm(ohaState) || ohaCodeProtectionLocked(ohaState)) {
        return;
    }

    if (ohaState?.Capabilities?.CodeRequired) {
        if (!ohaFocusInlineCodepad()) {
            ohaOpenCodepad();
        }
        return;
    }

    ohaRequestAction('Disarm', '');
}

function ohaClearCodeRequestTimer() {
    if (ohaCodeRequestTimer !== null) {
        window.clearTimeout(ohaCodeRequestTimer);
        ohaCodeRequestTimer = null;
    }
}

function ohaClearCodeLockTimer() {
    if (ohaCodeLockTimer !== null) {
        window.clearTimeout(ohaCodeLockTimer);
        ohaCodeLockTimer = null;
    }
}

function ohaScheduleCodeLockRefresh(state) {
    ohaClearCodeLockTimer();
    if (!ohaCodeProtectionLocked(state)) {
        return;
    }

    const remaining = Math.max(0, Number(state.CodeProtection?.LockoutRemaining) || 0);
    ohaCodeLockTimer = window.setTimeout(() => {
        ohaCodeLockTimer = null;
        ohaRequestAction('RefreshVisualization', true);
    }, Math.max(250, (remaining + 1) * 1000));
}

function ohaSetCodeError(message) {
    for (const error of document.querySelectorAll('[data-code-error]')) {
        error.hidden = message === '';
        error.textContent = message;
    }
}

function ohaUpdateCodepad() {
    const inputAllowed = ohaCodeInputAllowed();

    for (const display of document.querySelectorAll('[data-code-display]')) {
        const digits = display.querySelectorAll('.oha-code-dot');
        digits.forEach((dot, index) => {
            dot.dataset.filled = index < ohaCodeBuffer.length ? 'true' : 'false';
        });
        display.setAttribute(
            'aria-label',
            `${ohaTranslate('Code entry')}: ${ohaCodeBuffer.length} ${ohaTranslate('digits entered')}`
        );
    }

    const confirmEnabled = inputAllowed && !ohaCodeBusy && ohaCodeBuffer.length >= 4 && ohaCodeBuffer.length <= 8;
    for (const button of document.querySelectorAll('[data-code-confirm]')) {
        button.dataset.enabled = confirmEnabled ? 'true' : 'false';
        button.setAttribute('aria-disabled', confirmEnabled ? 'false' : 'true');
    }

    const editEnabled = inputAllowed && !ohaCodeBusy && ohaCodeBuffer.length > 0;
    for (const button of document.querySelectorAll('[data-code-delete], [data-code-clear]')) {
        button.dataset.enabled = editEnabled ? 'true' : 'false';
        button.setAttribute('aria-disabled', editEnabled ? 'false' : 'true');
    }

    const digitEnabled = inputAllowed && !ohaCodeBusy && ohaCodeBuffer.length < 8;
    for (const button of document.querySelectorAll('[data-code-digit]')) {
        button.dataset.enabled = digitEnabled ? 'true' : 'false';
        button.setAttribute('aria-disabled', digitEnabled ? 'false' : 'true');
    }
}

function ohaResetCodeEntry() {
    ohaClearCodeRequestTimer();
    ohaCodeBuffer = '';
    ohaCodeBusy = false;
    ohaSetCodeError('');
    ohaUpdateCodepad();
}

function ohaOpenCodepad() {
    if (!ohaCodeInputAllowed()) {
        return;
    }

    const overlay = document.getElementById('codepadOverlay');
    ohaResetCodeEntry();
    overlay.hidden = false;
    document.body.classList.add('oha-modal-open');

    window.requestAnimationFrame(() => {
        document.querySelector('#codepadOverlay [data-code-digit="1"]')?.focus();
    });
}

function ohaCloseCodepad() {
    const overlay = document.getElementById('codepadOverlay');
    if (overlay) {
        overlay.hidden = true;
    }
    document.body.classList.remove('oha-modal-open');
    ohaResetCodeEntry();
}

function ohaAppendCodeDigit(digit) {
    if (!ohaCodeInputAllowed() || ohaCodeBusy || !/^[0-9]$/.test(digit) || ohaCodeBuffer.length >= 8) {
        return;
    }

    ohaCodeBuffer += digit;
    ohaSetCodeError('');
    ohaUpdateCodepad();
}

function ohaDeleteCodeDigit() {
    if (!ohaCodeInputAllowed() || ohaCodeBusy || ohaCodeBuffer.length === 0) {
        return;
    }

    ohaCodeBuffer = ohaCodeBuffer.slice(0, -1);
    ohaSetCodeError('');
    ohaUpdateCodepad();
}

function ohaClearCodeEntry() {
    if (!ohaCodeInputAllowed() || ohaCodeBusy || ohaCodeBuffer.length === 0) {
        return;
    }

    ohaCodeBuffer = '';
    ohaSetCodeError('');
    ohaUpdateCodepad();
}

function ohaSubmitCode() {
    if (!ohaCodeInputAllowed() || ohaCodeBusy || ohaCodeBuffer.length < 4 || ohaCodeBuffer.length > 8) {
        return;
    }

    const code = ohaCodeBuffer;
    ohaCodeBuffer = '';
    ohaCodeBusy = true;
    ohaSetCodeError('');
    ohaUpdateCodepad();
    ohaRequestAction('DisarmWithCode', code);

    ohaClearCodeRequestTimer();
    ohaCodeRequestTimer = window.setTimeout(() => {
        ohaCodeRequestTimer = null;
        if (ohaCodeBusy) {
            ohaCodeBusy = false;
            ohaSetCodeError(ohaTranslate('No response from the alarm system. Please try again.'));
            ohaUpdateCodepad();
        }
    }, 5000);
}

function ohaHandleInteraction(interaction) {
    if (!interaction || interaction.Type !== 'disarm_code') {
        return;
    }

    ohaClearCodeRequestTimer();
    ohaCodeBusy = false;
    ohaCodeBuffer = '';

    if (interaction.Success === false) {
        ohaSetCodeError(ohaTranslate(
            interaction.Reason === 'locked'
                ? 'Code entry is temporarily locked.'
                : 'Code not accepted. Please try again.'
        ));
    } else {
        ohaSetCodeError('');
    }
    ohaUpdateCodepad();
}

function ohaFindInteractiveControl(event) {
    const target = event.target instanceof Element ? event.target : null;
    if (!target) {
        return null;
    }

    return target.closest(
        '[data-code-digit], [data-code-delete], [data-code-clear], [data-code-confirm], '
        + '[data-action="arm"], [data-operation], #disarmButton, #refreshButton, #codepadClose'
    );
}

function ohaActivateCodeControl(control) {
    if (!(control instanceof HTMLElement)) {
        return;
    }

    if (control.matches('[data-code-digit]')) {
        ohaAppendCodeDigit(control.getAttribute('data-code-digit') ?? '');
        return;
    }

    if (control.matches('[data-code-delete]')) {
        ohaDeleteCodeDigit();
        return;
    }

    if (control.matches('[data-code-clear]')) {
        ohaClearCodeEntry();
        return;
    }

    if (control.matches('[data-code-confirm]')) {
        ohaSubmitCode();
    }
}

function ohaHandleInteractiveClick(event) {
    const control = ohaFindInteractiveControl(event);
    if (!control) {
        return;
    }

    // Use the normal click event as the single source of truth. Symcon's tile
    // surface can suppress pointer events while still delivering click events.
    // Handling the click in the document capture phase makes every control work
    // before a parent tile handler can consume the event.
    event.preventDefault();

    if (control.matches('[data-code-digit], [data-code-delete], [data-code-clear], [data-code-confirm]')) {
        ohaActivateCodeControl(control);
        return;
    }

    if (control.matches('[data-action="arm"]')) {
        ohaHandleModeButton(control);
        return;
    }

    if (control.matches('[data-operation]')) {
        if (control.dataset.enabled !== 'true') {
            return;
        }
        const action = control.dataset.operation ?? '';
        const variableID = Number(control.dataset.variableId) || 0;
        ohaRequestAction(action, variableID > 0 ? variableID : true);
        return;
    }

    if (control.id === 'disarmButton') {
        ohaHandleDisarmButton();
        return;
    }

    if (control.id === 'refreshButton') {
        ohaRequestAction('RefreshVisualization', true);
        return;
    }

    if (control.id === 'codepadClose') {
        ohaCloseCodepad();
    }
}

function ohaBindInteractions() {
    document.addEventListener('click', ohaHandleInteractiveClick, true);
}

function ohaIsControlStatePayload(state) {
    return Boolean(
        state
        && typeof state === 'object'
        && Number(state.ApiVersion) === 1
        && typeof state.State?.Name === 'string'
        && typeof state.Mode?.Name === 'string'
        && state.Capabilities
        && typeof state.Capabilities === 'object'
        && typeof state.Capabilities.CodeRequired === 'boolean'
        && state.Modes
        && typeof state.Modes === 'object'
    );
}

function handleMessage(data) {
    let nextState = data;
    if (typeof data === 'string') {
        try {
            nextState = JSON.parse(data);
        } catch (_error) {
            return;
        }
    }

    // Keep the last complete control state until a new complete control-state
    // payload arrives. HTML-SDK can deliver unrelated/partial messages; replacing
    // ohaState with those would leave the rendered armed view visible while all
    // controls reject input until another full state update arrives.
    if (!ohaIsControlStatePayload(nextState)) {
        return;
    }

    const previousStateName = ohaState?.State?.Name ?? null;
    const nextStateName = nextState.State.Name;
    ohaState = nextState;

    if (nextStateName === 'disarmed' && previousStateName !== 'disarmed') {
        ohaResetCodeEntry();
    }

    ohaRender();
    ohaHandleInteraction(nextState?.Interaction);
}

document.getElementById('codepadOverlay').addEventListener('click', (event) => {
    if (event.target === event.currentTarget && !ohaCodeBusy) {
        ohaCloseCodepad();
    }
});

document.addEventListener('keydown', (event) => {
    const overlay = document.getElementById('codepadOverlay');
    const inlineCodepad = document.getElementById('inlineCodepad');
    const overlayOpen = !overlay.hidden;
    const inlineFocused = inlineCodepad.contains(document.activeElement);

    if ((!overlayOpen && !inlineFocused) || !ohaCodeInputAllowed()) {
        return;
    }

    if (/^[0-9]$/.test(event.key)) {
        event.preventDefault();
        ohaAppendCodeDigit(event.key);
        return;
    }

    if (event.key === 'Backspace') {
        event.preventDefault();
        ohaDeleteCodeDigit();
        return;
    }

    if (event.key === 'Enter') {
        event.preventDefault();
        ohaSubmitCode();
        return;
    }

    if (event.key === 'Escape' && !ohaCodeBusy) {
        event.preventDefault();
        if (overlayOpen) {
            ohaCloseCodepad();
        } else {
            ohaResetCodeEntry();
        }
    }
});

const ohaDesktopCodepadQuery = window.matchMedia('(min-width: 900px)');
if (typeof ohaDesktopCodepadQuery.addEventListener === 'function') {
    ohaDesktopCodepadQuery.addEventListener('change', (event) => {
        if (event.matches && !document.getElementById('codepadOverlay').hidden) {
            ohaCloseCodepad();
        }
    });
}

if (ohaIPSViewConfig) {
    document.addEventListener('visibilitychange', () => ohaScheduleIPSViewPoll(100));
    window.addEventListener('focus', () => ohaScheduleIPSViewPoll(100));
}

ohaInitializeAdaptiveTheme();
ohaBindInteractions();
ohaRender();
ohaScheduleIPSViewPoll(250);
