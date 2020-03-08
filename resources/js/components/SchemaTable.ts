import * as m from "mithril";

export default class SchemaTable implements m.Component {
    show = false;

    view(vnode) {
        return m('.card.mb-1', [
            m('a.card-header.text-body', {
                href: '#',
                onclick: event => {
                    event.preventDefault();
                    this.show = !this.show;
                },
            }, [
                vnode.attrs.table,
                m('i.float-right.fas.fa-chevron-' + (this.show ? 'up' : 'down')),
            ]),
            this.show ? m('table.table.table-sm', [
                m('thead', m('tr', [
                    m('th', 'Type'),
                    m('th', 'Column'),
                ])),
                m('tbody', Object.keys(vnode.attrs.columns).map(column => m('tr', [
                    m('td', m('code', vnode.attrs.columns[column])),
                    m('td', column),
                ]))),
            ]) : null,
        ]);
    }
}
