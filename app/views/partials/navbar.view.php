<div class="bg-white border-gray-200 dark:bg-gray-900">
  <nav class="bg-white border-gray-200 dark:bg-gray-900">
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
      <a href="<?= ROOT ?>/home" class="flex items-center space-x-3 rtl:space-x-reverse">
        <img src="<?= ROOT ?>/assets/images/scope.png" class="h-12" alt="Scope Logo" />
        <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">Sbox1</span>
      </a>
      <button data-collapse-toggle="navbar-default" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600" aria-controls="navbar-default" aria-expanded="false">
        <span class="sr-only">Open main menu</span>
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15" />
        </svg>
      </button>
      <div class="hidden w-full md:block md:w-auto" id="navbar-default">
        <ul class="font-medium flex flex-col p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:flex-row md:space-x-8 rtl:space-x-reverse md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">
          <li>
            <a href="<?= ROOT ?>/home" class="<?php $URL = URL(0);
                                              if ($URL === "home")
                                              {
                                                echo "md:dark:text-purple-500 ";
                                              }
                                              else
                                              {
                                                echo "dark:text-white ";
                                              } ?> block py-2 px-3 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:dark:hover:text-purple-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent" aria-current="page">Home</a>
          </li>
          <li>
            <a href="<?= ROOT ?>/about" class="<?php $URL = URL(0);
                                                if ($URL === "about")
                                                {
                                                  echo "md:dark:text-purple-500 ";
                                                }
                                                else
                                                {
                                                  echo "dark:text-white ";
                                                } ?> block py-2 px-3 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:dark:hover:text-purple-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">About</a>
          </li>
          <?php if (isset($_SESSION["USER"])): ?>
            <li>
              <a href="<?= ROOT ?>/book" class="<?php $URL = URL(0);
                                                if ($URL === "book")
                                                {
                                                  echo "md:dark:text-purple-500 ";
                                                }
                                                else
                                                {
                                                  echo "dark:text-white ";
                                                } ?> block py-2 px-3 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:dark:hover:text-purple-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">Booking</a>
            </li>
          <?php endif; ?>
          <?php if (isset($_SESSION["USER"]) && $_SESSION["USER"]->is_admin): ?>
            <li>
              <a href="<?= ROOT ?>/admin" class="<?php $URL = URL(0);
                                                  if ($URL === "admin")
                                                  {
                                                    echo "md:dark:text-purple-500 ";
                                                  }
                                                  else
                                                  {
                                                    echo "dark:text-white ";
                                                  } ?> block py-2 px-3 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:dark:hover:text-purple-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">Admin Panel</a>
            </li>
          <?php endif; ?>
          <li>
            <a href="<?= ROOT ?>/faq" class="<?php $URL = URL(0);
                                              if ($URL === "faq")
                                              {
                                                echo "md:dark:text-purple-500 ";
                                              }
                                              else
                                              {
                                                echo "dark:text-white ";
                                              } ?> block py-2 px-3 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:dark:hover:text-purple-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">FAQ</a>
          </li>
          <li>
            <a href="<?= ROOT ?>/contact" class="<?php $URL = URL(0);
                                                  if ($URL === "contact")
                                                  {
                                                    echo "md:dark:text-purple-500 ";
                                                  }
                                                  else
                                                  {
                                                    echo "dark:text-white ";
                                                  } ?> block py-2 px-3 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:dark:hover:text-purple-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">Contact</a>
          </li>
          <?php if (!empty($_SESSION['USER'])) : ?>
            <li>
              <a href="<?= ROOT ?>/logout" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-purple-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">Logout</a>
            </li>
          <?php endif ?>
          <?php if (empty($_SESSION['USER'])) : ?>
            <li>
              <a href="<?= ROOT ?>/login" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-purple-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">Login</a>
            </li>
          <?php endif ?>
        </ul>
      </div>
    </div>
  </nav>
