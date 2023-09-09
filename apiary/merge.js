'use strict';

const fs = require('fs');
const jsdom = require('jsdom');

const { JSDOM } = jsdom;

const embedJsFile = __dirname + '/embed.js';
const htmlFile = __dirname + '/api-description.html';

const embedJs = fs.readFileSync(embedJsFile).toString();
const html = fs.readFileSync(htmlFile).toString();

const dom = new JSDOM(html);

const styleElement = dom.window.document.createElement('style');
styleElement.textContent = 'html, body { margin: 0px; height: 100%; } #embed-container { height: 100%; }';
dom.window.document.head.append(styleElement);

const embedContainerElement = dom.window.document.createElement('div');
embedContainerElement.id = 'embed-container';
dom.window.document.body.prepend(embedContainerElement);

const embedJsScript = dom.window.document.getElementsByTagName('script')[0];
embedJsScript.removeAttribute('src');
embedJsScript.textContent = embedJs;

const script = dom.window.document.getElementsByTagName('script')[1];
const scriptLines = script.textContent.trim().split("\n");
scriptLines.splice(2, 0, ",\nelement: '#embed-container'");
const scriptString = scriptLines.join("\n");
script.textContent = scriptString;

fs.writeFileSync(htmlFile, dom.serialize());
