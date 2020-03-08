import * as m from "mithril";
import * as CM from "codemirror";
import "codemirror/mode/sql/sql";

interface Attrs {
    value: string
    onchange: (value: string) => void
}

export default class CodeMirror implements m.Component<Attrs> {
    lastValue = '';
    doc: CM.Doc = null;

    oninit(vnode: m.Vnode<Attrs>) {
        this.lastValue = vnode.attrs.value;
    }

    view(vnode: m.Vnode<Attrs>) {
        if (vnode.attrs.value !== this.lastValue) {
            this.lastValue = vnode.attrs.value;
            this.doc.setValue(vnode.attrs.value);
        }

        return m('div');
    }

    oncreate(vnode: m.VnodeDOM<Attrs>) {
        this.doc = CM(vnode.dom as HTMLElement, {
            value: vnode.attrs.value,
            lineNumbers: true,
            mode: 'sql',
        }).getDoc();

        // @ts-ignore
        this.doc.on('change', () => {
            const value = this.doc.getValue();

            // Prevent calling onchange when the value was just changed via the view method
            if (value === this.lastValue) {
                return;
            }

            this.lastValue = value;
            vnode.attrs.onchange(value);
        });
    }
}
