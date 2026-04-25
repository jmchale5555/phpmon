<?php include 'partials/header.view.php' ?>

<h1 class="text-center mb-4 text-4xl font-extrabold leading-none tracking-tight text-gray-900 md:text-5xl lg:text-6xl dark:text-white">Welcome to the home page</h1>
<p class="text-center mb-6 text-lg font-normal text-gray-500 lg:text-xl sm:px-16 xl:px-48 dark:text-gray-400">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p class="text-center mb-6 text-lg font-normal text-gray-500 lg:text-xl sm:px-16 xl:px-48 dark:text-gray-400">Welcome <?= $name ?></p>
<?php if (empty($_SESSION['USER'])) : ?>
    <a href="<?= ROOT ?>/login" class="ml-3 inline-flex items-center justify-center px-5 py-3 text-base font-medium text-center text-white bg-purple-700 rounded-lg hover:bg-purple-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900">
        Go to login
        <svg class="w-3.5 h-3.5 ml-2 ms-2 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
        </svg>
    </a>
<?php endif; ?>
<!-- <div x-data="{counter: 5}">
    <h2 class="text-gray-500" x-text="counter"></h2>
</div> -->
<div class="flex justify-center">
    <img src="<?= $funk ?>">
</div>
<?php include 'partials/footer.view.php' ?>