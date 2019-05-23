<h2 class="content__main-heading">Список задач</h2>

<form class="search-form" action="index.php" method="GET" autocomplete="off">
  <input class="search-form__input" type="text" name="search" value="<?= isset($_GET['search']) && !empty($_GET['search']) ? esc($_GET['search']) : '' ?>" placeholder="Поиск по задачам">

  <input class="search-form__submit" type="submit" name="" value="Искать">
</form>

<div class="tasks-controls">
  <nav class="tasks-switch">
    <a href="/index.php?filter=show_all" class="tasks-switch__item <?= $active_filter === 'show_all' ? ' tasks-switch__item--active' : '' ?>">Все задачи</a>
    <a href="/index.php?filter=today" class="tasks-switch__item <?= $active_filter === 'today' ? ' tasks-switch__item--active' : '' ?>">Повестка дня</a>
    <a href="/index.php?filter=tomorrow" class="tasks-switch__item <?= $active_filter === 'tomorrow' ? ' tasks-switch__item--active' : '' ?>">Завтра</a>
    <a href="/index.php?filter=out_of_date" class="tasks-switch__item <?= $active_filter === 'out_of_date' ? ' tasks-switch__item--active' : '' ?>">Просроченные</a>
  </nav>

  <label class="checkbox">
    <!--добавить сюда аттрибут "checked", если переменная $show_complete_tasks равна единице-->
    <input class="checkbox__input visually-hidden show_completed" type="checkbox" <?= $show_complete_tasks ? 'checked': '' ?> >
    <span class="checkbox__text">Показывать выполненные</span>
  </label>
</div>

<table class="tasks">

  <?php if (isset($tasks)): ?>
    <?php foreach ($tasks as $value) :
      if ($value['done'] !== 1 || $show_complete_tasks) : ?>
        <tr class="tasks__item task
          <?= (int)$value['done'] === 1 ? 'task--completed' : '' ?>
          <?= (int)$value['done'] === 0 && is_important($value['date']) ? 'task--important' : '' ?>
        ">
          <td class="task__select">
            <label class="checkbox task__checkbox">
              <input class="checkbox__input visually-hidden task__checkbox" type="checkbox" value="<?= esc($value['id']) ?>" <?= $value['done'] ? 'checked' : '' ?>>
              <span class="checkbox__text"><?= esc($value['task']) ?></span>
            </label>
          </td>          
          
          <td class="task__file">
            <?php if (isset($value['file'])) : ?>
              <a class="download-link" href="<?= $value['file'] ?? '' ?>"><?= mb_strimwidth($value['file'], mb_strwidth('/uploads/'), mb_strwidth($value['file'])) ?></a>
            <?php endif ?>
          </td>          

          <td class="task__date"><?= $value['date'] ?></td>
        </tr>
      <?php endif ?>
    <?php endforeach ?>
    <?php else : ?>
        <p>Ничего не найдено по вашему запросу</p>
  <?php endif ?>
</table>
