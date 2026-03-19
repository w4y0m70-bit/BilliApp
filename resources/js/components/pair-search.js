
export default function pairSearch() {
    return {
        searchQuery: '',
        results: [],
        selectedUser: null,
        searching: false,

        async searchUsers() {
            if (this.searchQuery.length < 2) {
                this.results = [];
                return;
            }
            this.searching = true;
            try {
                // 絶対パスで指定しておくと安心です
                const response = await fetch(`/user/search-users?query=${encodeURIComponent(this.searchQuery)}`);
                this.results = await response.json();
                console.log(this.results);
            } catch (e) {
                console.error("ユーザー検索に失敗しました:", e);
            } finally {
                this.searching = false;
            }
        },

        selectUser(user) {
            this.selectedUser = user;
            this.results = [];
            this.searchQuery = '';
        },

        clearSelection() {
            this.selectedUser = null;
        }
    }
}