import * as m from "mithril";
import App from "../utils/App";
import CodeMirror from "./CodeMirror";
import ResultsTable from "./ResultsTable";

export default class Composer implements m.Component<{ savedQuery?: SavedQuery }> {
    sql = '-- Here\'s a quick example to get you started\n-- Check out the documentation on the left for the available tables\n\n-- Get the last 10 extension updates published\nselect package, version, date, title, description from releases\nwhere version != \'latest\'\norder by date desc\nlimit 10';
    title = '';
    public = true;
    last: Query = null;
    dirty = false;
    running = false;
    saving = false;
    savedQuery: SavedQuery = null;

    oninit(vnode) {
        if (vnode.attrs.savedQuery) {
            this.savedQuery = vnode.attrs.savedQuery;
            this.sql = vnode.attrs.savedQuery.sql;
            this.run();
        }
    }

    run() {
        this.running = true;

        App.request<Query>({
            method: 'POST',
            url: '/api/queries',
            body: {
                sql: this.sql,
                fromSaved: this.savedQuery ? this.savedQuery.uid : null,
            },
        }).then(query => {
            this.dirty = false;
            this.running = false;
            this.last = query;
            m.redraw();
        }).catch(e => {
            this.running = false;
            m.redraw();
            throw e;
        });
    }

    view(vnode) {
        return m('.card', [
            m('.card-header', m('.form-inline', [
                m('input[type=text].form-control', {
                    value: this.title,
                    oninput: event => {
                        this.title = event.target.value;
                    },
                    placeholder: this.savedQuery ? this.savedQuery.title : 'Untitled',
                    readonly: !!this.savedQuery,
                    disabled: this.running || this.saving,
                }),
                this.savedQuery ? [
                    this.public ? null : m('em.ml-2', 'Private snippet'),
                    m('a.btn.btn-secondary.ml-2', {
                        href: m.route.get(),
                        onclick: event => {
                            // @ts-ignore
                            if (navigator.clipboard) {
                                event.preventDefault();
                                // @ts-ignore
                                navigator.clipboard.writeText(window.location);
                            }
                        },
                    }, [
                        m('i.fas.fa-share'),
                        ' Copy permalink',
                    ]),
                ] : [
                    m('.custom-control.custom-switch.ml-2', [
                        m('input[type=checkbox].custom-control-input#public', {
                            checked: this.public,
                            onchange: () => {
                                this.public = !this.public;
                            },
                            disabled: this.running || this.saving,
                        }),
                        m('label.custom-control-label[for=public]', 'Public'),
                    ]),
                    m('button[type=button].btn.btn-secondary.ml-2', {
                        onclick: () => {
                            if (this.dirty || !this.last) {
                                alert('Please run your query before saving it');
                                return;
                            }

                            if (this.last.error) {
                                alert('You cannot save a query that threw an error');
                                return;
                            }

                            if (!this.title) {
                                alert('Please give your query a title');
                                return;
                            }

                            this.saving = true;

                            App.request<SavedQuery>({
                                method: 'POST',
                                url: '/api/saved-queries',
                                body: {
                                    query: this.last.uid,
                                    title: this.title,
                                    public: this.public,
                                },
                            }).then(savedQuery => {
                                App.store.unshift(savedQuery);
                                m.route.set('/q/' + savedQuery.uid);
                            }).catch(e => {
                                this.saving = false;
                                m.redraw();
                                throw e;
                            });
                        },
                        disabled: this.running || this.saving,
                    }, [
                        m('i.fas.fa-' + (this.saving ? 'spinner fa-pulse' : 'save')),
                        ' Create permalink',
                    ]),
                    vnode.attrs.savedQuery ? m('button[type=button].btn.btn-secondary.ml-2', {
                        onclick: () => {
                            this.savedQuery = vnode.attrs.savedQuery;
                            this.sql = vnode.attrs.savedQuery.sql;
                            this.dirty = false;
                            this.last = null;
                        },
                        disabled: this.running || this.saving,
                    }, [
                        m('i.fas.fa-undo'),
                        ' Revert changes',
                    ]) : null,
                ],
                m('button[type=button].btn.btn-primary.ml-auto', {
                    onclick: () => {
                        this.run();
                    },
                    disabled: this.running || this.saving,
                }, [
                    m('i.fas.fa-' + (this.running ? 'spinner fa-pulse' : 'play')),
                    ' Run',
                ]),
            ])),
            m(CodeMirror, {
                value: this.sql,
                onchange: value => {
                    this.sql = value;
                    this.dirty = true;
                    this.savedQuery = null;
                    m.redraw();
                },
            }),
            this.last ? m('.card-body', this.last.error ? [
                m('.alert.alert-danger', this.last.error),
            ] : (this.last.results.length ? [
                this.last.auto_limited && this.last.results.length === 100 ? m('.alert.alert-warning', 'A LIMIT 100 clause has been automatically applied to your request') : null,
                m('p.text-muted', this.last.results.length + ' rows'),
                m(ResultsTable, {
                    results: this.last.results,
                }),
            ] : m('p', 'No results'))) : null,
        ]);
    }
}
