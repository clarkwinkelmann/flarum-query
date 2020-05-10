import * as m from "mithril";
import App from "../utils/App";

export default class Footer implements m.Component {
    oninit(vnode) {
        vnode.state.copyrightDate = (new Date).getFullYear();
    }

    view(vnode) {
        return m('footer', m('.container', [
            m('.row', [
                m('.col-md-6', [
                    m('h5', 'About'),
                    m('p', [
                        'Query is a free and ',
                        m('a', {href: 'https://github.com/clarkwinkelmann/flarum-query'}, 'open-source'),
                        ' service made for the Flarum community by ',
                        m('a', {href: 'https://clarkwinkelmann.com/'}, 'Clark Winkelmann'),
                        ' to share insights into the usage of the ',
                        m('a', {href: 'https://flarum.org/docs/extend/'}, 'Extension API'),
                        '.',
                    ]),
                    m('p', [
                        'The ',
                        m('a', {href: 'https://flarum.org/'}, 'Flarum'),
                        ' name and logo are property of the Flarum Foundation. ',
                        'This website is not affiliated with the Flarum team.',
                    ]),
                ]),
                m('.col-md-3', [
                    m('h5', 'Support'),
                    m('ul', [
                        m('li', m('a', {href: App.discuss}, 'Discuss thread')),
                        m('li', m('a', {href: 'https://github.com/clarkwinkelmann/flarum-query/issues'}, 'Open a GitHub issue')),
                    ]),
                ]),
                m('.col-md-3', [
                    m('h5', 'Ecosystem'),
                    m('ul', [
                        m('li', m('a', {href: 'https://flarum.org/'}, 'Flarum Foundation')),
                        m('li', m('a', {href: 'https://extiverse.com/'}, 'Extiverse')),
                        m('li', m('a', {href: 'https://www.freeflarum.com/'}, 'FreeFlarum')),
                        m('li', m('a', {href: 'https://lab.migratetoflarum.com/'}, 'MigrateToFlarum Lab')),
                        m('li', m('a', {href: 'https://builtwithflarum.com/'}, 'Built With Flarum')),
                    ]),
                ]),
            ]),
            m('p.text-center.text-muted.mt-3', '© Clark Winkelmann ' + vnode.state.copyrightDate),
        ]));
    }
}
