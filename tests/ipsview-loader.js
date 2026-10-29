'use strict';

const assert = require('node:assert/strict');
const {dashboardURL, start} = require('../OpenHomeAlarm/visualization/ipsview-loader.js');

const configuration = {
    path: '/hook/openhomealarm/12345',
    token: 'a'.repeat(32)
};
const opaqueIPSViewDocument = {
    location: {origin: 'null', href: 'data:text/html,OpenHomeAlarm'},
    document: {baseURI: 'http://symcon.example.invalid:3777/console/'},
    parent: null
};

assert.equal(
    dashboardURL(configuration, opaqueIPSViewDocument),
    'http://symcon.example.invalid:3777/hook/openhomealarm/12345?view=dashboard&token=' + 'a'.repeat(32),
    'An opaque IPSView WebContent document must resolve the dashboard against its Symcon HTTP base.'
);
assert.equal(
    dashboardURL(configuration, {location: {origin: 'https://symcon.example.invalid'}, document: {}}),
    'https://symcon.example.invalid/hook/openhomealarm/12345?view=dashboard&token=' + 'a'.repeat(32),
    'A normal HTTP document must preserve its Symcon origin.'
);
assert.equal(
    dashboardURL({...configuration, path: '//foreign.example.invalid/hook'}, opaqueIPSViewDocument),
    null,
    'A protocol-relative or foreign endpoint must be rejected.'
);
assert.equal(
    dashboardURL({...configuration, token: 'invalid'}, opaqueIPSViewDocument),
    null,
    'An invalid dashboard token must be rejected before navigation.'
);
assert.equal(
    dashboardURL(configuration, {location: {origin: 'null', href: 'data:text/html,test'}, document: {baseURI: 'data:text/html,test'}}),
    null,
    'A missing Symcon HTTP origin must remain a visible loader error path.'
);

let replacedURL = null;
const message = {hidden: true, textContent: ''};
const loaderEnvironment = {
    location: {
        origin: 'null',
        href: 'data:text/html,OpenHomeAlarm',
        replace(url) {
            replacedURL = url;
        }
    },
    document: {
        baseURI: 'http://symcon.example.invalid:3777/console/',
        getElementById(id) {
            if (id === 'oha-ipsview-config') return {textContent: JSON.stringify(configuration)};
            if (id === 'oha-ipsview-error') return message;
            return null;
        }
    },
    parent: null
};
start(loaderEnvironment);
assert.equal(
    replacedURL,
    'http://symcon.example.invalid:3777/hook/openhomealarm/12345?view=dashboard&token=' + 'a'.repeat(32),
    'The WebContent document itself must navigate to the stable dashboard origin.'
);
assert.equal(message.hidden, true, 'A successful dashboard navigation must not expose the loader error.');

console.log('IPSView loader tests passed.');
