<?php

session_start();

header("Content-Type: application/json");

class TechnoBot {

    private array $intents;

    public function __construct() {

        $this->intents = [

            "hallo" => [

                "keywords" => [
                    "hallo",
                    "salam",
                    "helo",
                    "hey",
                    "hi",
                    "hii",
                    "yo",
                    "wsg",
                    "wsp",
                    "sup",
                    "goede avond",
                    "goede morgen",
                    "hoe gaat het"
                ],

                "responses" => [

                    "hallo" => [
                        "Hallo 👋 Hoe kan ik je helpen?",
                        "Hey 😄 Wat kan ik voor je doen?",
                        "Hi daar!",
                        "Hallo 👋",
                        "Heyyy 😎"
                    ],
                     "hoe gaat het" => [
                        "Met mij gaat het goed! En met jou? 😄",
                        "Alles chill! En met jou? 😎"
                    ],
                     "salam" => [
                        "Salam! Hoe kan ik je helpen?",
                        "Salam! Yooo",
                    ],

                    "wsg" => [
                        "Wsg gang 😎",
                        "Yooo wat is goed 🔥",
                        "Wsg bro 👊"
                    ],

                    "wsp" => [
                        "Wsp 😄",
                        "Alles chill?",
                        "Wsp bro 👊"
                    ],

                    "hey" => [
                        "Hey 👋",
                        "Heey 😄",
                        "Yo!"
                    ]
                ],

                "follow_up" => [
                    "Dat heb je al gezegd",
                    "Nog een keer? 😄",
                    "Haha hallo opnieuw 👋",
                ]
            ],

            "feeling" => [

                "keywords" => [
                    "sad",
                    "happy",
                    "tired",
                    "angry",
                    "depressed",
                    "excited",
                    "moe",
                    "blij"
                ],

                "responses" => [
                    "Ik hoop dat alles goed komt ❤️",
                    "Vertel me meer 😄",
                    "Dat klinkt interessant!",
                    "Ik luister 👀",
                    "Gevoelens zijn belangrijk 🙂"
                ],

                "follow_up" => [
                    "Nog steeds daar mee bezig?",
                    "Ik snap het 😄",
                    "Thanks dat je het deelt 👊"
                ]
            ]
        ];
    }

    public function respond(string $message): string {

        $message = strtolower(trim($message));

        foreach($this->intents as $intentName => $intent) {

            foreach($intent["keywords"] as $keyword) {

                if($this->matches($message, $keyword)) {

                    return $this->getResponse(
                        $intentName,
                        $intent,
                        $message
                    );
                }
            }
        }

        return $this->defaultResponse();
    }

    private function matches(string $message, string $keyword): bool {

        if(str_contains($message, $keyword)) {
            return true;
        }

        $words = explode(" ", $message);

        foreach($words as $word) {

            if(abs(strlen($word) - strlen($keyword)) > 2) {
                continue;
            }

            $distance = levenshtein($word, $keyword);

            $maxLength = max(strlen($word), strlen($keyword));

            if($maxLength === 0) {
                continue;
            }

            $similarity = 1 - ($distance / $maxLength);

            if($similarity >= 0.75) {
                return true;
            }
        }

        return false;
    }

    private function getResponse(
        string $intentName,
        array $intent,
        string $message
    ): string {

        if($intentName === "hallo") {

            if(str_contains($message, "wsg")) {
                $responses = $intent["responses"]["wsg"];

            } elseif(str_contains($message, "wsp")) {
                $responses = $intent["responses"]["wsp"];

            } elseif(str_contains($message, "salam")) {
                $responses = $intent["responses"]["salam"];

            } elseif(str_contains($message, "hey")) {
                $responses = $intent["responses"]["hey"];

            } elseif(str_contains($message, "hoe gaat het")) {
                $responses = $intent["responses"]["hoe gaat het"];

            } else {
                $responses = $intent["responses"]["hallo"];
            }

        } else {

            $responses = $intent["responses"];
        }

        $index = rand(0, count($responses) - 1);

        return $responses[$index];
    }

    private function defaultResponse(): string {

        $responses = [
            "Sorry 😅 Dat snap ik nog niet.",
            "Hmm 🤔 Kun je het anders formuleren?",
            "Ik leer nog 👀",
            "Interessant... vertel meer 😄"
        ];

        return $responses[rand(0, count($responses) - 1)];
    }
}

$message = $_POST["message"] ?? "";

$bot = new TechnoBot();

echo json_encode([
    "reply" => $bot->respond($message)
]);