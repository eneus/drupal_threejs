/**/

function $$(x) {
    return document.getElementById(x);
}

// Add indexOf to IE.
if(!Array.indexOf){
    Array.prototype.indexOf = function(obj){
        for(var i=0; i<this.length; i++){
            if(this[i]==obj){
                return i;
            }
        }
        return -1;
    }
}

var canvas, gl;

function launchLogo() {
    initializeLogo(canvas);
}

function log(msg) {
    var d = document.createElement("div");
    d.appendChild(document.createTextNode(msg));
    document.body.appendChild(d);
}

function removeClass(element, clas) {
    // Does not work in IE var classes = element.getAttribute("class");
    var classes = element.className;
    if (classes) {
        var cs = classes.split(/\s+/);
        if (cs.indexOf(clas) != -1) {
            cs.splice(cs.indexOf(clas), 1);
        }
        // Does not work in IE element.setAttribute("class", cs.join(" "));
        element.className = cs.join(" ");
    }
}

function addClass(element, clas) {
    element.className = element.className + " " + clas;
}

function pageLoaded() {
    removeClass($$("have-javascript"), "webgl-hidden");
    addClass($$("no-javascript"), "webgl-hidden");
    b = BrowserDetect;
    b.init();
    canvas = document.getElementById("webgl-logo");
    var ratio = (window.devicePixelRatio ? window.devicePixelRatio : 1);
    canvas.width = 140 * ratio;
    canvas.height = 150 * ratio;
    var experimental = false;
    try { gl = canvas.getContext("webgl"); }
    catch (x) { gl = null; }

    if (gl == null) {
        try { gl = canvas.getContext("experimental-webgl"); experimental = true; }
        catch (x) { gl = null; }
    }

    if (gl) {
        // hide/show phrase for webgl-experimental
        $$("webgl-experimental").style.display = experimental ? "auto" : "none";

        // Set the support link to the correct URL for the browser.
        $$("support-link").href = b.urls.troubleshootingUrl;

        // show webgl supported div, and launch webgl demo
        removeClass($$("webgl-yes"), "webgl-hidden");
        launchLogo();
    } else if ("WebGLRenderingContext" in window) {
        // not a foolproof way to check if the browser
        // might actually support WebGL, but better than nothing
        removeClass($$("webgl-disabled"), "webgl-hidden");

        // Do we know this browser?
        if (b.browser != "unknown") {
            // Yes. So show the known-browser message.
            removeClass($$("known-browser"), "webgl-hidden");

            // Hide the unknonw-browser message.
            addClass($$("unknown-browser"), "webgl-hidden");

            // Set the correct link for troubleshooting.
            $$("troubleshooting-link").href = b.urls.troubleshootingUrl;
        }
    } else {
        // Show the no webgl message.
        removeClass($$("webgl-no"), "webgl-hidden");

        // Do we know the browser and can it be upgraded?
        if (b.browser != "unknown" && b.urls.upgradeUrl) {
            // Yes, show the browser and the upgrade link.
            $$("name").innerHTML = b.browser;
            $$("upgrade-link").href = b.urls.upgradeUrl;
        } else {
            // No, so only the link for browser for this platform.
            randomizeBrowsers();
            addClass($$("upgrade-browser"), "webgl-hidden");
            removeClass($$("get-browser"), "webgl-hidden");
            $$("platform").innerHTML = b.platform;
        }
    }
}

function randomizeBrowsers() {

    var bl = $$("webgl-browser-list");
    var browsers = [];
    var infos = b.platformInfo.browsers;
    for (var ii = 0; ii < infos.length; ++ii) {
      browsers.push({
        info: infos[ii],
        weight: Math.random()
      });
    }

    browsers = browsers.sort(function(a, b) {
        if (a.weight < b.weight) return -1;
        if (a.weight > b.weight) return 1;
        return 0;
    });

    for (var ii = 0; ii < browsers.length; ++ii) {
        var info = browsers[ii].info;
        var div = document.createElement("p");
        var a = document.createElement("a");
        a.href = info.url;
        a.innerHTML = info.name;
        div.appendChild(a);
        bl.appendChild(div);
    }
}

// addEventListener does not work on IE7/8.
window.onload = pageLoaded;

