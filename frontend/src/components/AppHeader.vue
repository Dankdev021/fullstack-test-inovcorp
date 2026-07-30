<template>
  <header class="sticky top-0 z-30 border-b border-brand-dark bg-brand text-white shadow-sm">
    <div class="mx-auto flex h-14 max-w-7xl items-center px-4 sm:h-16 sm:px-6 lg:px-8">
      <RouterLink to="/" class="flex items-center gap-3 rounded-sm font-semibold">
        <span class="grid size-8 place-items-center rounded-sm bg-white text-brand">
          <svg viewBox="0 0 24 24" class="size-5" aria-hidden="true">
            <path
              fill="currentColor"
              d="M12 2.5 21.5 8 12 13.5 2.5 8 12 2.5Zm-7.5 8.8 7.5 4.3 7.5-4.3v4.4L12 20 4.5 15.7v-4.4Z"
            />
          </svg>
        </span>
        <span class="text-base tracking-tight sm:text-lg">Task Manager</span>
      </RouterLink>
      <span class="ml-auto hidden text-sm text-blue-100 sm:block">Projetos e tarefas em um só lugar</span>
    </div>
  </header>
</template>
