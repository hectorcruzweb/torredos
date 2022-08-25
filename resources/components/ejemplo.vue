<template>
<div>
    <div v-for="lis in list" :key="lis.id">
        {{ lis.email }}
    </div>
</div>
</template>

<script>
export default {
    created: function () {
        this.getUsers();
    },
    data: {
        list: []
    },
    methods: {
        getUsers: function () {
            axios.get('https://jsonplaceholder.typicode.com/users').then(response => {
                this.list = response.data
                toastr.success('ok');
            });
        }
    },
}
</script>
