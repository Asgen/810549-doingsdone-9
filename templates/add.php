  <h2 class="content__main-heading">Добавление задачи</h2>

  <form class="form"  action="add.php" method="post" enctype="multipart/form-data" autocomplete="off">
    <div class="form__row">
      <label class="form__label" for="name">Название <sup>*</sup></label>

      <input class="form__input <?= $errors['name'] ? 'form__input--error' : '' ?>" type="text" name="name" id="name" value="<?= $task['name'] ?>" placeholder="Введите название">
      <?php if (isset($errors['name'])) : ?>
        <p class='form__message'><?= $errors['name'] ?></p>
      <?php endif ?>
    </div>

    <div class="form__row">
      <label class="form__label" for="project">Проект <sup>*</sup></label>

      <select class="form__input form__input--select <?= $errors['project'] ? 'form__input--error' : '' ?>" name="project" id="project">
        <?php foreach ($projects as $value) : ?>
        <option value="<?= $value['project_id'] ?>" <?= $value['project_id'] === $task['project'] ? 'selected' : '' ?>><?= $value['category'] ?></option>
        <?php endforeach ?>
      </select>
      <?php if (isset($errors['project'])) : ?>
        <p class='form__message'><?= $errors['project'] ?></p>
      <?php endif ?>
    </div>

    <div class="form__row">
      <label class="form__label" for="date">Дата выполнения</label>
      <input class="form__input form__input--date <?= $errors['date'] ? 'form__input--error' : '' ?>" type="text" name="date" id="date" value="<?= $task['date'] ?>" placeholder="Введите дату в формате ГГГГ-ММ-ДД">
      <?php if (isset($errors['date'])) : ?>
        <p class='form__message'><?= $errors['date'] ?></p>
      <?php endif ?>
    </div>

    <div class="form__row">
      <label class="form__label" for="file">Файл</label>

      <div class="form__input-file">
        <input class="visually-hidden" type="file" name="file" id="file" value="">

        <label class="button button--transparent" for="file">
          <span>Выберите файл</span>
        </label>
      </div>
    </div>

    <div class="form__row form__row--controls">
      <input class="button" type="submit" name="" value="Добавить">
    </div>
  </form>
</main>
