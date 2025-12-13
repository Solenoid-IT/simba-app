<svelte:head>
    <title>404 - PAGE NOT FOUND</title>
</svelte:head>

<script>

    import '@/app.css';
    import '@/app.custom.css';

</script>

<div class="blk">
    <!-- 404 Error Text -->
    <div class="text-center">
        <div class="error mx-auto">404</div>
        <p class="lead text-gray-800 mb-5">Page Not Found</p>
        <p class="text-gray-500 mb-0">It looks like you found a glitch in the matrix...</p>
        <a href="/">&larr; Back to Dashboard</a>
    </div>
</div>

<style>

    :global( body )
    {
        background-color: var( --simba-dark );
    }

    .blk
    {
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

</style>