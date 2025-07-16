<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контактная форма</title>
    <link rel="stylesheet" href="./app.css">
    
</head>
<body>
    <div class="form-container">
        <h1>Оставьте заявку</h1>
        <form action="handler.php" method="POST">
            <div class="form-group">
                <label for="name">Ваше имя</label>
                <input required type="text" name="name" id="name" placeholder="Иван Иванов">
            </div>
            
            <div class="form-group">
                <label for="phone">Телефон</label>
                <input required type="text" name="phone" id="phone" placeholder="+7 (999) 123-45-67">
            </div>
            
            <div class="form-group">
                <label for="comment">Комментарий</label>
                <textarea required name="comment" id="comment" placeholder="Ваш комментарий..."></textarea>
            </div>
            
            <button name="submit" type="submit">Отправить заявку</button>
        </form>
    </div>
</body>
</html>
