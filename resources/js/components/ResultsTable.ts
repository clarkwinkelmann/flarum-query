import * as m from "mithril";

function renderValue(value, column) {
    if (value === null) {
        return m('em.text-muted', 'NULL');
    }

    if (column === 'package' && /^[A-Za-z0-9_-]+\/[A-Za-z0-9_-]+$/.test(value)) {
        return m('a.text-body', {
            href: 'https://packagist.org/packages/' + value,
            target: '_blank',
            rel: 'noopener nofollow',
        }, value);
    }

    if (column === 'discuss' && /^https:\/\/discuss\.flarum\.org\/$/.test(value)) {
        return m('a.text-body', {
            href: value,
            target: '_blank',
            rel: 'noopener nofollow',
        }, value);
    }

    return value;
}

export default class ResultsTable implements m.Component<{ results: object[] }> {
    view(vnode) {
        return m('table.ResultsTable.table.table-striped.table-sm', [
            m('thead', m('tr', Object.keys(vnode.attrs.results[0]).map(column => m('th', column)))),
            m('tbody', vnode.attrs.results.map(
                row => m('tr', Object.keys(row).map(
                    column => m('td', renderValue(row[column], column))
                ))
            )),
        ]);
    }
}
