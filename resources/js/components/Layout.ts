import * as m from "mithril";
import App from "../utils/App";
import schema from "../schema";
import SchemaTable from "./SchemaTable";
import ResultsTable from "./ResultsTable";

export default class Layout implements m.Component {
    showDocs = true;

    view(vnode) {
        const queries = App.store.filter(query => query.public);

        return m('.app', {
            'data-url': m.route.get(),
        }, [
            m('header', [
                m('nav.navbar.navbar-expand-lg.navbar-dark.bg-dark', m('.container', [
                    m(m.route.Link, {
                        href: '/',
                        className: 'navbar-brand',
                    }, [m('i.fas.fa-code'), ' Flarum Query']),
                    m('button.navbar-toggler[type=button][data-toggle=collapse][data-target=#navbar][aria-controls=navbar][aria-expanded=false][aria-label=Toggle navigation]', m('span.navbar-toggler-icon')),
                    m('#navbar.collapse.navbar-collapse', [
                        m('ul.navbar-nav.ml-auto', [
                            App.discuss ? m('li.nav-item', m('a.nav-link', {
                                href: App.discuss,
                                target: '_blank',
                                rel: 'noopener',
                            }, ['Discuss thread ', m('i.fas.fa-external-link-alt')])) : null,
                        ]),
                    ]),
                ])),
            ]),
            m('.container.py-3', m('.row', [
                m('.col-lg-9.order-lg-2', vnode.children),
                m('.col-lg-3.order-lg-1', [
                    m('ul.nav.nav-pills.mb-2', [
                        m('li.nav-item', m('a.nav-link', {
                            href: '#',
                            className: this.showDocs ? 'active' : '',
                            onclick: () => {
                                this.showDocs = true;
                            },
                        }, 'Documentation')),
                        App.showLatest ? m('li.nav-item', m('a.nav-link', {
                            href: '#',
                            className: this.showDocs ? '' : 'active',
                            onclick: () => {
                                this.showDocs = false;
                            },
                        }, 'Latest')) : null,
                    ]),
                    this.showDocs ? [
                        m('p', 'The following tables can be queried:'),
                        Object.keys(schema).map(table => m(SchemaTable, {
                            table,
                            columns: schema[table],
                        })),
                    ] : [
                        m('p', 'The latest snippets shared by users'),
                        queries.map(query => m('.card.mt-2', m('.card-body', [
                            m('h5', query.title),
                            query.created_at ? m('p', 'Created ' + query.created_at.split('T')[0]) : null,
                            m('details', [
                                m('h6', 'SQL'),
                                m('pre', query.sql),
                                m('h6', 'Preview'),
                                m(ResultsTable, {
                                    results: query.preview,
                                }),
                            ]),
                            m(m.route.Link, {
                                className: 'btn btn-secondary',
                                href: '/q/' + query.uid,
                            }, 'Load'),
                        ]))),
                    ],
                ]),
            ])),
        ]);
    }
}
