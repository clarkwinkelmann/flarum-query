interface Query {
    uid: string
    sql: string
    error: string
    results: object[]
    auto_limited: boolean
}

interface SavedQuery {
    uid: string
    title: string
    sql: string
    preview: object[]
    public: boolean
    created_at: string
}
