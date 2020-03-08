import * as m from "mithril";
import Composer from "./Composer";

export default class HomePage implements m.Component {
    view(vnode) {
        return m(Composer);
    }
}
