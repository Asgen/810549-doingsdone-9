<h2 class="content__main-heading">Вход на сайт</h2>

<form class="form" action="auth.php" method="post" autocomplete="off">
  <div class="form__row">
    <label class="form__label" for="email">E-mail <sup>*</sup></label>

    <input class="form__input" type="text" name="email" id="email" value="<?= esc($form['email'] ?? '') ?>" placeholder="Введите e-mail">
  </div>

  <div class="form__row">
    <label class="form__label" for="password">Пароль <sup>*</sup></label>

    <input class="form__input" type="password" name="password" id="password" value="" placeholder="Введите пароль">
  </div>

  <div class="form__row form__row--controls">
    <?php if (isset($errors['email']) || isset($errors['name'])) : ?>
      <p class="error-message">Вы ввели неверный email/пароль</p>
    <?php endif ?>

    <input class="button" type="submit" name="" value="Войти">
  </div>
</form>