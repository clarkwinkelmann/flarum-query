import * as m from "mithril";
import Layout from "./components/Layout";
import App from "./utils/App";
import HomePage from "./components/HomePage";
import QueryPage from "./components/QueryPage";

try {
    // @ts-ignore
    window.Popper = require('@popperjs/core').default;
    // @ts-ignore
    window.$ = window.jQuery = require('jquery');

    require('bootstrap/js/src/carousel');
    require('bootstrap/js/src/dropdown');
    require('bootstrap/js/src/tooltip');
} catch (e) {
}

let isFirstMatch = true;

function createResolver(component) {
    return {
        onmatch(args, requestedPath) {
            if (!isFirstMatch) {
                // Tracking pages via javascript starting with the second page (first is tracked via the js in HTML)
                // @ts-ignore
                if (window._paq) {
                    // @ts-ignore
                    window._paq.push(['setCustomUrl', requestedPath]);
                    // @ts-ignore
                    window._paq.push(['trackPageView']);
                }
            }

            isFirstMatch = false;
        },
        render: () => {
            return m(Layout, m(component, {
                key: m.route.get(),
            }));
        },
    };
}

const root: HTMLDivElement = document.querySelector('#app');

if (root) {
    App.boot(root);

    m.route.prefix = '';

    m.route(root, '/', {
        '/': createResolver(HomePage),
        '/q/:id': createResolver(QueryPage),
    });

    document.getElementById('loading').style.display = 'none';
}
