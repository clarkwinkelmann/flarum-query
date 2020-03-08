import * as m from "mithril";

const App = {
    csrfToken: '',
    discuss: '',
    showLatest: '',
    store: [] as SavedQuery[],
    boot(root: HTMLDivElement): void {
        App.csrfToken = root.dataset.token;
        App.discuss = root.dataset.discuss;
        App.showLatest = root.dataset.showLatest;
        App.store = JSON.parse(root.dataset.queries) as SavedQuery[];
    },
    request<T>(options: m.RequestOptions<any> & { url: string }): Promise<T> {
        if (options.method !== 'GET') {
            options.headers = {
                'X-CSRF-TOKEN': this.csrfToken,
            };
        }

        return m.request(options).catch(err => {
            if (err.code === 422) {
                const errors = [];

                Object.keys(err.response.errors).forEach(key => {
                    err.response.errors[key].forEach(message => {
                        errors.push('- ' + message);
                    });
                });

                alert('Validation errors:\n' + errors.join('\n'));
            } else {
                throw err;
            }
        });
    },
};

export default App;
