import * as m from "mithril";
import App from "../utils/App";
import Composer from "./Composer";

export default class QueryPage implements m.Component {
    savedQuery: SavedQuery = null;

    oninit() {
        const uid = m.route.param('id');

        this.savedQuery = App.store.find(query => query.uid === uid);
    }

    view(vnode) {
        if (!this.savedQuery) {
            return m('div', [
                m('i.fas.fa-spinner.fa-pulse'),
                ' Loading...',
            ]);
        }

        return m(Composer, {
            savedQuery: this.savedQuery,
        });
    }
}
