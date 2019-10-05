<?php

class Replies
{
    private function addPositive($msg)
    {
        $arr = [
            "",
            " :)",
            ")",
            " :p",
            "!"
        ];
        return $msg . $arr[array_rand($arr)];
    }
    
    private function addNegative($msg)
    {
        $arr = [
            "",
            " :(",
            "(",
            ".",
            ".."
        ];
        return $msg . $arr[array_rand($arr)];
    }
    public function enableSuccess()
    {
        $arr = [
            "Я включалась",
            "Включилась",
            "Запущена",
            "Запуск прошёл успешно",
            "Включилась, аниме уже на подходе"
        ];
        $msg = $arr[array_rand($arr)];
        return $this->addPositive($msg);
    }

    public function enableFail()
    {
        $arr = [
            "Я уже включена",
            "Да включена я",
            "Включена уже"
        ];
        $msg = $arr[array_rand($arr)];
        return $this->addPositive($msg);
    }

    public function disableSuccess()
    {
        $arr = [
            "Выключилась",
            "Я выключилась",
            "Отключилась",
            "Я отключилась",
            "Выключилась, больше об аниме не говорим",
            "Выключилсь. Но может всё-таки включите"
        ];
        $msg = $arr[array_rand($arr)];
        return $this->addNegative($msg);
    }

    public function disableFail()
    {
        $arr = [
            "Как я могу выключиться, если уже выключена?",
            "Вообще-то я и так выключена!",
            "Повторное выключение не дало эффекта."
        ];
        return $arr[array_rand($arr)];
    }

    public function clear()
    {
        $arr = [
            "Фильтры почистила, буду сообщать о всех новых сериях",
            "Все фильтры были очищены",
            "Почистила фильтры",
            "Почистила"
        ];
        $msg = $arr[array_rand($arr)];
        return $this->addPositive($msg);
    }

    public function dontUnderstand()
    {
        $arr = [
            "Я вас не понимаю",
            "Я еще глупенькая и не понимаю таких слов",
            "Чего-чего?",
            "Не понимаю я вас, извините",
            "Я не понимаю",
            "Не понимаю я вас"
        ];
        if (rand(1, 100) > 98)
            $msg = "Я тут чтобы вам об аниме сообщать а не разговоры разговаривать!";
        else if (rand(1, 500) > 495) {
            return $this->generateRandomString(rand(10, 1000));
        }
        else
            $msg = $arr[array_rand($arr)];
        $msg = $this->addNegative($msg);
        $msg .= PHP_EOL . "Справка /help";
        return $msg;
    }

    public function help()
    {
        $arr = [
            "Пока я работаю, я буду присылать уведомления о всех-всех-всех новых сериях! " .
            "Но вы можете добавить несколько фильтров, и тогда я буду фильтровать релизы " .
            "только по этим ключевым словам.",
            
            "Пока я работаю, я буду присылать уведомления о всех новых сериях. " .
            "Но если вы добавите несколько фильтров, то я буду фильтровть по ним релизы. "
        ];
        $msg = $arr[array_rand($arr)] . PHP_EOL . PHP_EOL;

        $arr = [
            "Вот какие команды у меня есть: ",
            "Список команд:",
            "У меня есть вот такие команды:",
            "Пожалуйста, ознакомьтесь с моими командами"
        ];
        $hereislist = $arr[array_rand($arr)];
        if ($hereislist == $arr[3])
            $hereislist = $this->addPositive($hereislist);
        $msg .= $hereislist;
        $msg .=  PHP_EOL . PHP_EOL;
        $msg .= $this->helpCommands();
        return $msg;
    }

    public function help2()
    {
        $msg = $this->help2Commands();
        return $msg;
    }

    private function helpCommands()
    {
        $msg = '';
        $msg .= $this->formatDescription("/start", "Включить.");
        $msg .= $this->formatDescription("/stop", "Выключить.");
        $msg .= $this->formatDescription("/filter", "Показать текущие фильтры.");
        $msg .= $this->formatDescription("/add <code>[фильтр1, фильтр2, ...]</code>",
                                         "Добавить указанные фильтры.");
        $msg .= $this->formatDescription("/del <code>[фильтр1, фильтр2, ...]</code>",
                                         "Удалить указанные фильтры. ".
                                         "Можно вписать только часть слова, " .
                                         "удалю все совпадения.");
        $msg .= $this->formatDescription("/clear", "Очистить фильтры.");
        $msg .= "Дополнительные команды - /help2";
        return $msg;
    }

    private function help2Commands()
    {
        $msg = '';
        $msg .= $this->formatDescription("/help", "Основная справка.");
        $msg .= $this->formatDescription("/convert <code>[текст]</code>",
                                         "Конвертировать текст после команды из *.ass " .
                                         "в human-friendly вариант.");
        $msg .= "/creator";
        return $msg;
    }

    private function formatDescription($command, $description)
    {
        return $command . PHP_EOL . $description . PHP_EOL . PHP_EOL;
    }

    private function generateRandomString($length)
    {
        $characters = ' 0123456789abcdefghijklmnopqrstuvwxyz' .
                    'ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-=+/\'"';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
