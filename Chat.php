<?php

session_start();

header("Content-Type: application/json");

class TechnoBot
{

    private array $intents;
    private array $conversationHistory = [];
    private int $messageCount = 0;
    private array $synonyms = [];
    private array $semanticGroups = [];
    private array $suggestionMap = []; // Maps normalized suggestions to [intent, subTopic]

    // Tie threshold: if top scores are within this range, treat as a tie
    private const TIE_THRESHOLD = 0.08;

    public function __construct()
    {
        if (!isset($_SESSION["conversation_history"])) {
            $_SESSION["conversation_history"] = [];
        }
        $this->conversationHistory = $_SESSION["conversation_history"];
        $this->messageCount = count($this->conversationHistory);

        $this->initializeSynonyms();
        $this->initializeSemanticGroups();

        $this->intents = [

            "hallo" => [
                "keywords" => [
                    "hallo",
                    "hoi",
                    "salam",
                    "helo",
                    "hey",
                    "hi",
                    "hii",
                    "yo",
                    "wsp",
                    "wsg",
                    "goede avond",
                    "goede morgen",
                    "hoe gaat het"
                ],
                "answer" => "Hallo! Waar kan ik je mee helpen?",
                "suggestions" => [
                    "Wat is Technolab?",
                    "Wat is fika",
                    "Hoe werkt de boekhouding?"
                ]
            ],
            "technolab" => [
                "keywords" => [
                    "technolab",
                    "techno",
                    "lab",
                    "projecten",
                    "wat doen jullie",
                    "wat voor projecten",
                    "hoe groot",
                    "hoe werkt",
                    "wat kan ik vragen"
                ],
                "answer" => "Technolab Leiden is een leerwerkbedrijf met passie voor onderwijs, techniek, wetenschap en talentontwikkeling.",
                "suggestions" => [
                    "Hoe groot is Technolab?",
                    "Wat voor projecten doen jullie?",
                    "Hoe werkt Technolab?"
                ],
                "sub_topics" => [
                    "groot" => [
                        "keywords" => ["groot", "hoeveel", "hoeveel scholen", "hoe groot", "hoe groot is"],
                        "answer" => "Ruim 36.000 leerlingen, meer dan 50 scholen en circa 100 bedrijven en organisaties doen elk jaar mee aan de lessen en projecten van Technolab! 🏫",
                    ],
                    "wie" => [
                        "keywords" => ["wie is technolab", "wie zijn wij", "wat is technolab", "wat zijn jullie"],
                        "answer" => "Bij Technolab verbinden we onderwijs, techniek en talentontwikkeling. Samen met scholen en bedrijven laten we kinderen, jongeren én medewerkers ontdekken: wie ben ik, wat kan ik, wat wil ik? We maken ze enthousiast voor natuur en techniek: de toekomst! 🌟",
                    ],
                    "projecten" => [
                        "keywords" => ["wat doen jullie", "wat voor projecten", "wat voor projecten doen jullie", "projecten"],
                        "answer" => "We organiseren workshops, lessen (zoals TechniekWijs, ToekomstTaal en Toekomstkunde), POP-UP projectweken, beroepsoriëntatietrajecten (Talent & Toekomst), de Willie Wortel Wedstrijd, de Meesterchallenge en Expeditie Leerkracht. Ook bieden we stages en leerwerkplekken voor mbo-, hbo- en wo-studenten! 🚀",
                    ],
                    "hoe_werkt" => [
                        "keywords" => ["hoe werkt technolab", "hoe werkt dit", "hoe werkt"],
                        "answer" => "Bij Technolab leer je door te doen! Leerlingen, studenten en professionals werken samen in een creatieve omgeving. We geven lessen op locatie in Leiden én op scholen (POP-UP). Medewerkers werken in cirkels (holacratie) en nemen verantwoordelijkheid voor hun eigen rol. Elke dag zijn er zo'n 20 stagiairs actief aan het werk! 💡",
                    ],
                    "wat_kan_vragen" => [
                        "keywords" => ["wat kan ik vragen", "wat kun je beantwoorden", "waarmee kan je helpen", "wat weet jij"],
                        "answer" => "Je kunt mij vragen over van alles rondom Technolab! Denk aan: Fika, BHV, pasjes, pensioen, MDT, loon, VOG, huisregels, urenregistratie, holacratie, de dagco, lessen en leskisten, stage lopen, en nog veel meer. Probeer het gewoon! 😄",
                    ],
                ]
            ],

            "oke" => [
                "keywords" => [
                    "Sorry, ik bedoelde het niet",
                    "Ik snap het niet",
                ],
                "answer" => "Oke, geen probleem! Waar kan ik je mee helpen?",
                "suggestions" => []
            ],

            "feeling" => [
                "keywords" => [
                    "verdrietig",
                    "happy",
                    "tired",
                    "boos",
                    "depressed",
                    "excited",
                    "moe",
                    "blij",
                    "gevoelens"
                ],
                "answer" => "Ik luister. Gevoelens zijn belangrijk.",
                "suggestions" => []
            ],

            "onaardig" => [
                "keywords" => [
                    "dom",
                    "dumb",
                    "stom",
                    "idioot",
                    "sukkel",
                    "lul",
                    "homo",
                    "bitch",
                    "niet leuk",
                    "niet aardig",
                    "niet interessant",
                    "niet belangrijk",
                    "niet belangrijk genoeg",
                    "schelden",
                    "grof"
                ],
                "answer" => "Dat is niet zo aardig 😔",
                "suggestions" => [
                    "Sorry, ik bedoelde het niet"
                ]
            ],

            "hadj" => [
                "keywords" => [
                    "goat",
                    "anis",
                    "hadj",
                    "moussa",
                    "greatest",
                    "algerijnse"
                ],
                "answer" => "Anissssss🐐",
                "suggestions" => [
                    "Anis Hadj Moussa?",
                ],
                "sub_topics" => [
                    "wie" => [
                        "keywords" => ["wie is anis hadj", "anis hadj", "wie is anis", "wie is de hadj goat"],
                        "answer" => "",
                        "image" => "Images/Anissss.gif"
                    ],
                ]
            ],
            "big D" => [
                "keywords" => [
                    "Big D",
                    "big d",
                    "dayaan"
                ],
                "answer" => "",
                "image" => "Images/DiddyD.jpg"
            ],

            // ── DAGCO ─────────────────────────────────────────────────────────
            "dagco" => [
                "keywords" => ["dagco", "dagcoordinator", "dagcoördinator", "dag coordinator"],
                "answer" => "De dagcoördinator (dagco) zorgt elke dag dat Technolab op tijd open is en de dag goed verloopt. 🗓️",
                "suggestions" => [
                    "Wat doet de dagco?",
                    "Hoe bereik ik de dagco?",
                    "Wie is de dagco vandaag?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["dagco wat", "dagco doet", "wat doet de dagco", "dagco taken"],
                        "answer" => "De dagco zorgt dat Technolab op tijd open is, zet koffie en thee klaar, opent de dag in de kring met een energiser en houdt gedurende de dag bij wie het pand verlaat of terugkomt. Ook regel je vervoer via de dagco! 🚐☕",
                    ],
                    "bereiken" => [
                        "keywords" => ["dagco bellen", "dagco bereiken", "dagco nummer", "hoe bereik ik de dagco", "dagco telefoon"],
                        "answer" => "De dagco is bereikbaar op 071-5191324. Bel bij ziekte of verhindering tussen 08:10 en 08:25 uur! 📞",
                    ],
                    "wie" => [
                        "keywords" => ["dagco wie", "wie is dagco", "wie is de dagco vandaag", "dagco vandaag"],
                        "answer" => "Elke dag is er iemand uit een ander team dagco. Kijk op de planner of vraag het aan een collega wie het vandaag is! 👀",
                    ],
                    "sleutel" => [
                        "keywords" => ["dagco sleutel", "dagco alarm", "dagco openen"],
                        "answer" => "Als dagco krijg je een sleutel van Technolab en wordt uitgelegd hoe het alarm werkt 🔑",
                    ],
                ]
            ],

            // ── LESKISTEN ─────────────────────────────────────────────────────
            "leskisten" => [
                "keywords" => ["leskisten", "leskist", "blauwe kisten", "blauwe leskist"],
                "answer" => "De blauwe leskisten zijn de herkenbare kisten waarmee Technolab lessen worden gegeven! 📦",
                "suggestions" => [
                    "Wat zitten er in de leskisten?",
                    "Welke lessen horen bij de leskisten?",
                    "Waar worden leskisten gebruikt?"
                ],
                "sub_topics" => [
                    "inhoud" => [
                        "keywords" => ["leskist inhoud", "leskist wat zit erin", "wat zitten er in de leskisten", "leskist materiaal"],
                        "answer" => "In de blauwe leskisten zit al het materiaal voor een Technolab les: handleidingen, materialen voor experimenten en opdrachten. Alles wat je nodig hebt voor een goede les zit erin! 🔬🔧",
                    ],
                    "lessen" => [
                        "keywords" => ["leskist lessen", "welke lessen leskisten", "leskist techniekwijs", "welke lessen horen bij de leskisten"],
                        "answer" => "De leskisten horen bij de lessen van Technolab, zoals TechniekWijs (wetenschap & techniek), ToekomstTaal (programmeren & mediawijsheid) en Toekomstkunde (duurzaamheid & technologie). 📚",
                    ],
                    "gebruik" => [
                        "keywords" => ["leskist gebruik", "leskist waar", "leskist school", "waar worden leskisten gebruikt"],
                        "answer" => "Leskisten worden gebruikt bij lessen op Technolab zelf én bij POP-UP lessen op scholen in de regio Leiden. Zo brengen we de Technolab-ervaring direct naar de klas! 🏫",
                    ],
                ]
            ],

            // ── STAGE LOPEN ───────────────────────────────────────────────────
            "stage" => [
                "keywords" => ["stage", "stagiair", "stage lopen", "stageplaats"],
                "answer" => "Bij Technolab Leiden kun je een actieve en afwisselende stage lopen in een creatieve omgeving! 🎓",
                "suggestions" => [
                    "Hoe meld ik me aan voor een stage?",
                    "Wat kan ik doen tijdens mijn stage?",
                    "Voor welke studierichtingen is stage mogelijk?"
                ],
                "sub_topics" => [
                    "aanmelden" => [
                        "keywords" => ["stage aanmelden", "stage inschrijven", "stage formulier", "stage aanvragen", "hoe meld ik me aan voor een stage"],
                        "answer" => "Aanmelden kan via het formulier op technolableiden.nl/over-technolab/stage-leiden/. Daarna volg je de stappen: aanmelden → inspiratiemiddag → ontdekdag → match & start! 📝",
                    ],
                    "wat_doen" => [
                        "keywords" => ["stage wat doe je", "stage werkzaamheden", "stage activiteiten", "wat kan ik doen tijdens mijn stage"],
                        "answer" => "Tijdens je stage werk je mee aan inspirerend techniekonderwijs voor kinderen en jongeren. Je werkt in een multidisciplinair team, krijgt ruimte voor eigen ideeën en begeleiding gericht op jouw leerdoelen. Elke dag zijn er zo'n 20 stagiairs actief! 💪",
                    ],
                    "richtingen" => [
                        "keywords" => ["stage richtingen", "stage opleiding", "stage welke studie", "voor welke studierichtingen is stage mogelijk"],
                        "answer" => "Technolab zoekt stagiairs uit diverse richtingen, zoals Toegepaste Psychologie, HBO-ICT / Innovative Development, Media Vormgeven en MLO. De mix van achtergronden zorgt voor een inspirerende leeromgeving! 🎨💻",
                    ],
                    "contact" => [
                        "keywords" => ["stage contact", "stage email", "stage vragen", "stage bellen"],
                        "answer" => "Vragen over je stage? Bel 071-5191324 of mail naar stage@technolableiden.nl. Let op: tijdens schoolvakanties wordt mail minder vaak gelezen 📧",
                    ],
                ]
            ],

            // ── LESSEN ────────────────────────────────────────────────────────
            "lessen" => [
                "keywords" => ["les", "lessen", "lesaanbod", "lesprogramma", "workshop", "workshops", "techniekwijs", "toekomsttaal", "toekomstkunde", "popup", "willie wortel"],
                "answer" => "Technolab biedt inspirerende lessen en workshops voor PO en VO op het gebied van techniek, wetenschap en talentontwikkeling! 📚",
                "suggestions" => [
                    "Wat zijn alle lesprogramma's?",
                    "Wat is TechniekWijs?",
                    "Wat is ToekomstTaal?"
                ],
                "sub_topics" => [
                    "overzicht" => [
                        "keywords" => ["les overzicht", "alle lessen", "welke lessen", "wat zijn de lesprogrammas van technolab", "lesprogrammas"],
                        "answer" => "Technolab heeft drie hoofdleerlijnen: TechniekWijs (wetenschap & techniek), ToekomstTaal (programmeren & mediawijsheid) en Toekomstkunde (duurzaamheid & technologie). Daarnaast zijn er POP-UP projectweken, mini-stages en de Willie Wortel Wedstrijd! 🔭💻🌱",
                    ],
                    "techniekwijs" => [
                        "keywords" => ["techniekwijs", "wetenschap techniek les", "wat is techniekwijs"],
                        "answer" => "TechniekWijs is de leerlijn voor wetenschap en techniekonderwijs. Met een rijk aanbod aan apparatuur halen we de uitvinder in leerlingen naar boven! Denk aan workshops over stroomcircuits, katrollen, tandwielen en elektriciteit. 🔧⚡",
                    ],
                    "toekomsttaal" => [
                        "keywords" => ["toekomsttaal", "programmeren les", "mediawijsheid les", "wat is toekomsttaal", "digispel"],
                        "answer" => "ToekomstTaal is de leerlijn voor programmeren en mediawijsheid. Leerlingen leren hoe digitale technologie werkt, programmeren met Micro:bit en bouwen eigen robots! 🤖📱",
                    ],
                    "toekomstkunde" => [
                        "keywords" => ["toekomstkunde", "duurzaamheid les", "klimaat les", "wat is toekomstkunde", "groen doen"],
                        "answer" => "Toekomstkunde gaat over duurzaamheid, hernieuwbare energie, biodiversiteit en klimaatactie. Leerlingen werken hands-on aan oplossingen voor duurzaamheidsvraagstukken. 🌿🌍",
                    ],
                    "popup" => [
                        "keywords" => ["popup les", "pop up technolab", "projectweek school", "les op school"],
                        "answer" => "Met POP-UP Technolab komen wij naar jouw school! We verzorgen een week vullend programma met activiteiten op school én bijzondere dagdelen bij Technolab of de Hortus. Er is een programmeer/maakweek én een uitvindersweek. Afgesloten met een tentoonstelling! 🎉",
                    ],
                    "willie_wortel" => [
                        "keywords" => ["willie wortel", "uitvinderswedstrijd", "wedstrijd technolab"],
                        "answer" => "De Willie Wortel Wedstrijd is een uitvinderswedstrijd voor jonge geniën uit de regio Leiden. Ze bedenken creatieve oplossingen voor uiteenlopende problemen. Er is ook een junior versie voor jongere leerlingen! 🏆💡",
                    ],
                ]
            ],

            // ── FIKA ──────────────────────────────────────────────────────────
            "fika" => [
                "keywords" => ["fika"],
                "answer" => "Fika is een gezamenlijke lunch elke woensdag waarbij een team kookt voor iedereen. 🍽️",
                "suggestions" => [
                    "Wie kookt er bij fika?",
                    "Hoeveel is het fika budget?",
                    "Wat eten we bij fika?"
                ],
                "sub_topics" => [
                    "wanneer" => [
                        "keywords" => ["fika wanneer", "fika dag", "fika woensdag"],
                        "answer" => "Fika is elke woensdag 📅"
                    ],
                    "budget" => [
                        "keywords" => ["fika budget", "fika geld", "fika kosten", "fika hoeveel", "hoeveel is het fika budget"],
                        "answer" => "Er mag rond de €50,- voor Fika worden uitgegeven 💶"
                    ],
                    "boodschappen" => [
                        "keywords" => ["fika boodschappen", "fika winkel", "fika plus"],
                        "answer" => "Boodschappen worden meestal bij de Plus beneden gedaan 🛒"
                    ],
                    "eten" => [
                        "keywords" => ["fika eten", "fika koken wat", "fika vegetarisch", "fika veganistisch", "fika alcohol", "wat eten we bij fika"],
                        "answer" => "We koken veganistisch/vegetarisch 🌱 en consumeren geen alcohol."
                    ],
                    "team" => [
                        "keywords" => ["fika team", "wie kookt fika", "fika kookt", "wie kookt er bij fika"],
                        "answer" => "Na elke Fika wordt door behulp van een rad een nieuw team gekozen, plus nieuwe ingrediënten🍳",
                        "image" => "Images/Fika.png"
                    ],
                    "verhindering" => [
                        "keywords" => ["fika verhinderd", "fika vervanging", "fika geen tijd"],
                        "answer" => "Ben je gekozen maar heb je geen tijd? Zoek dan zelf vervanging! 🔄"
                    ],
                ]
            ],

            // ── BHV ───────────────────────────────────────────────────────────
            "bhv" => [
                "keywords" => ["bhv", "bedrijfshulpverlening"],
                "answer" => "BHV staat voor BedrijfsHulpVerlening. BHV'ers helpen bij noodgevallen. 🚨",
                "suggestions" => [
                    "Wie zijn de BHV'ers?",
                    "Wanneer is de BHV training?",
                    "Wat zijn de BHV regels?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["bhv wat", "bhv uitleg", "bhv wat is"],
                        "answer" => "BHV staat voor BedrijfsHulpVerlening. BHV'ers zijn aanwezig om te helpen bij noodgevallen 🚨"
                    ],
                    "wie" => [
                        "keywords" => ["bhv wie", "bhv aanwezig", "bhv board", "bhv knus", "wie zijn de bhv'ers"],
                        "answer" => "Wie op dit moment BHV'er is, staat op het zwarte board in de knus 🖤",
                        "image" => "Images/BHV.jpg"
                    ],
                    "training" => [
                        "keywords" => ["bhv training", "bhv wanneer training", "bhv opleiding", "wanneer is de bhv training"],
                        "answer" => "Elk jaar volgen medewerkers een BHV training 📚"
                    ],
                    "regels" => [
                        "keywords" => ["bhv regels", "bhv noodgeval", "bhv wat doen", "wat zijn de bhv regels"],
                        "answer" => "Lees de regels goed door zodat je weet wat te doen is bij een noodgeval 📋"
                    ],
                ]
            ],

            // ── PASJE / SLEUTEL ───────────────────────────────────────────────
            "pasje" => [
                "keywords" => ["pasje", "liftpas", "badge"],
                "answer" => "Je kunt een pasje aanvragen bij de coördinator medewerkers. 🔑",
                "suggestions" => [
                    "Hoe vraag ik een pasje aan?",
                    "Wat is de liftpas?",
                    "Wat zijn de regels voor nachtwerk met een pasje?"
                ],
                "sub_topics" => [
                    "aanvragen" => [
                        "keywords" => ["pasje aanvragen", "pasje krijgen", "hoe pasje", "badge aanvragen", "hoe vraag ik een pasje aan"],
                        "answer" => "Je kunt een pasje aanvragen bij de coördinator medewerkers. 🔑",
                        "image" => "Images/Pasje.jpg"
                    ],
                    "liftpas" => [
                        "keywords" => ["liftpas wat", "liftpas toegang", "liftpas openen", "lift pasje", "wat is de liftpas"],
                        "answer" => "Sommige medewerkers hebben een liftpas waarmee je de lift kunt gebruiken. Ook hiermee kun je de deur van het gebouw openen."
                    ],
                    "sleutel" => [
                        "keywords" => ["pasje sleutel", "sleutel alarm", "alarm code pasje", "dagco sleutel"],
                        "answer" => "Als dagco krijg je een sleutel van Technolab en wordt uitgelegd hoe het alarm werkt."
                    ],
                    "nacht" => [
                        "keywords" => ["pasje nacht", "pasje weekend", "pasje vakantie", "pasje laat werken", "nachtwerk pasje", "wat zijn de regels voor nachtwerk met een pasje"],
                        "answer" => "Tussen 23:00-06:00 uur, weekend of vakantie? Informeer Bernard van Da Vinci College. Anders krijgt Technolab een boete! 📞"
                    ],
                ]
            ],

            // ── PENSIOEN ──────────────────────────────────────────────────────
            "pensioen" => [
                "keywords" => ["pensioen", "brightpensioen"],
                "answer" => "Technolab biedt geen collectieve pensioenregeling, maar BrightPensioen lidmaatschap wordt vergoed! 💰",
                "suggestions" => [
                    "Wat is BrightPensioen?",
                    "Hoe meld ik me aan voor pensioen?",
                    "Wat kost het pensioen?"
                ],
                "sub_topics" => [
                    "regeling" => [
                        "keywords" => ["pensioen regeling", "pensioen collectief", "collectieve pensioen"],
                        "answer" => "Technolab biedt geen collectieve pensioenregeling 📋"
                    ],
                    "bright" => [
                        "keywords" => ["brightpensioen wat", "bright pensioen uitleg", "pensioen bright", "wat is brightpensioen"],
                        "answer" => "BrightPensioen lidmaatschap wordt door Technolab vergoed zolang je een arbeidscontract hebt 💙"
                    ],
                    "aanmelden" => [
                        "keywords" => ["pensioen aanmelden", "bright aanmelden", "pensioen formulier", "hoe meld ik me aan voor pensioen"],
                        "answer" => "Ga naar de coördinator medewerker voor het aanmeldformulier 📝"
                    ],
                    "kosten" => [
                        "keywords" => ["pensioen kosten", "pensioen prijs", "pensioen wat kost", "wat kost het pensioen"],
                        "answer" => "BrightPensioen lidmaatschap wordt volledig vergoed door Technolab 💰"
                    ],
                ]
            ],

            // ── MDT ───────────────────────────────────────────────────────────
            "mdt" => [
                "keywords" => ["mdt", "maatschappelijke diensttijd"],
                "answer" => "MDT staat voor Maatschappelijke DienstTijd. Ben je onder de 30? Dan kan je hiervan profiteren! 📋",
                "suggestions" => [
                    "Voor wie is MDT?",
                    "Hoe registreer ik mijn MDT uren?",
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["mdt wat", "mdt uitleg", "mdt wat is"],
                        "answer" => "MDT staat voor Maatschappelijke DienstTijd. Technolab krijgt subsidie voor MDT uren."
                    ],
                    "wie" => [
                        "keywords" => ["mdt wie", "mdt leeftijd", "mdt jonger", "mdt 30", "mdt voor wie", "voor wie is mdt"],
                        "answer" => "Ben je jonger dan 30 jaar? Ga naar de MDT coördinator om een formulier in te vullen."
                    ],
                    "uren" => [
                        "keywords" => ["mdt uren", "mdt registreren", "mdt schrijven", "mdt wekelijks", "hoe registreer ik mijn mdt uren"],
                        "answer" => "Registreer wekelijks je gewerkte uren. Uren ophopen of compenseren is niet de bedoeling! ⏱️"
                    ],
                ]
            ],

            // ── LOONVERKLARING ────────────────────────────────────────────────
            "loon" => [
                "keywords" => ["loon", "loonverklaring", "salaris", "loonstrook", "betaling salaris", "betaling loon", "uitbetaling"],
                "answer" => "Je loon wordt via een boekhoudingsbureau betaald. Je hebt een loonverklaring én ID kopie nodig. 💳",
                "suggestions" => [
                    "Loon betaling?",
                    "Wat heb ik nodig voor mijn loon?",
                    "Naar wie stuur ik mijn loonverklaring?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["loon hoe", "loon betaling", "salaris betaling", "hoe werkt betaling", "hoe werkt de loon betaling", "hoe wordt mijn loon betaald", "hoe werkt salaris"],
                        "answer" => "De betaling van je loon gaat via een boekhoudingsbureau."
                    ],
                    "nodig" => [
                        "keywords" => ["loon nodig", "loon identiteitsbewijs", "loonverklaring nodig", "salaris nodig", "wat heb ik nodig voor mijn loon", "wat heb ik nodig salaris"],
                        "answer" => "Voor je uitbetaling is een loonverklaring én een kopie van je ID nodig."
                    ],
                    "sturen" => [
                        "keywords" => ["loonverklaring sturen", "loon sturen", "loon email", "loon naar wie", "naar wie stuur ik mijn loonverklaring", "salaris email"],
                        "answer" => "Stuur je loonverklaring naar boekhouding@technolableiden.nl. Zorg dat het op tijd aankomt!"
                    ],
                ]
            ],

            // ── VOG ───────────────────────────────────────────────────────────
            "vog" => [
                "keywords" => ["vog", "verklaring omtrent gedrag"],
                "answer" => "Een VOG (Verklaring Omtrent Gedrag) is verplicht om in het onderwijs te werken. 🏫",
                "suggestions" => [
                    "Wie vraagt de VOG aan?",
                    "Wat doe ik als ik mijn VOG ontvang?",
                    "Naar wie stuur ik mijn VOG?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["vog wat", "vog uitleg", "verklaring omtrent gedrag wat"],
                        "answer" => "Een VOG (Verklaring Omtrent Gedrag) is verplicht om in het onderwijs te werken 🏫"
                    ],
                    "aanvragen" => [
                        "keywords" => ["vog aanvragen", "vog technolab aanvragen", "vog wie vraagt", "wie vraagt de vog aan"],
                        "answer" => "De VOG wordt voor jou aangevraagd door Technolab 👍"
                    ],
                    "ontvangen" => [
                        "keywords" => ["vog ontvangen", "vog doorsturen", "vog coördinator", "vog sturen", "wat doe ik als ik mijn vog ontvang", "naar wie stuur ik mijn vog"],
                        "answer" => "Na ontvangst stuur je de VOG door naar de coördinator medewerker 📬"
                    ],
                ]
            ],

            // ── HUISREGELS ────────────────────────────────────────────────────
            "huisregels" => [
                "keywords" => ["huisregels"],
                "answer" => "Zorg dat je rond 8:15 uur binnen bent, dan starten we samen om 8:30 uur. 🕗",
                "suggestions" => [
                    "Wat doe ik als ik ziek ben volgens de huisregels?",
                    "Wat mag niet volgens de huisregels?",
                    "Welke klusjes horen bij de huisregels?"
                ],
                "sub_topics" => [
                    "tijd" => [
                        "keywords" => ["huisregels tijd", "huisregels beginnen", "huisregels 8:15", "huisregels 8:30"],
                        "answer" => "Zorg dat je rond 8:15 uur binnen bent, dan starten we samen om 8:30 uur 🕗"
                    ],
                    "ziek" => [
                        "keywords" => ["huisregels ziek", "huisregels verhinderd", "huisregels ziekmelden", "wat doe ik als ik ziek ben volgens de huisregels"],
                        "answer" => "Bel tussen 8:10 en 8:25 uur naar de dagco: 071-5191324 en zeg het je stagebegeleider 📞"
                    ],
                    "gedrag" => [
                        "keywords" => ["huisregels gedrag", "huisregels kauwgom", "huisregels telefoon", "huisregels pet", "wat mag niet volgens de huisregels"],
                        "answer" => "Geen kauwgom, telefoon in tas, geen pet in de les, privé blijft privé. 🙅"
                    ],
                    "verlaten" => [
                        "keywords" => ["huisregels verlaten", "huisregels weggaan", "huisregels pand"],
                        "answer" => "Verlaat je het pand? Meld het altijd bij de dagco 🚪"
                    ],
                    "klusjes" => [
                        "keywords" => ["huisregels klusjes", "huisregels opruimen", "huisregels taken", "welke klusjes horen bij de huisregels"],
                        "answer" => "Klusjes zoals opruimen horen erbij — wij zijn 1 team, 1 taak 💪"
                    ],
                ]
            ],

            // ── URENREGISTRATIE ───────────────────────────────────────────────
            "urenregistratie" => [
                "keywords" => ["urenregistratie", "uren", "registreren", "uren schrijven"],
                "answer" => "Registreer wekelijks je gewerkte uren. Uren ophopen of compenseren is niet de bedoeling! ⏱️",
                "suggestions" => [
                    "hoe werkt de urenregistratie?",
                    "Mag ik uren opbouwen via urenregistratie?",
                    "Hoe pas ik mijn werkschema aan via urenregistratie?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["uren schrijven hoe", "hoe werkt de urenregistratie"],
                        "answer" => "Registreer wekelijks je gewerkte uren. Uren ophopen of compenseren is niet de bedoeling!⏱️"
                    ],
                    "opbouwen" => [
                        "keywords" => ["urenregistratie opbouwen", "uren compenseren", "uren ophopen", "mag ik uren opbouwen via urenregistratie"],
                        "answer" => "Uren opbouwen of compenseren is niet de bedoeling ❌ Bespreek meer werken met je rolverdeler."
                    ],
                    "schema" => [
                        "keywords" => ["urenregistratie schema", "uren schema aanpassen", "werkschema aanpassen", "werkschema veranderen", "hoe pas ik mijn werkschema aan via urenregistratie"],
                        "answer" => "Wijzigingen in het werkschema worden van tevoren afgesproken met de rolverdeler 📋"
                    ],
                ]
            ],

            // ── TECHNOLABBER ──────────────────────────────────────────────────
            "technolabber" => [
                "keywords" => ["technolabber", "technolab cultuur", "technolab waarden", "technolab gedragscode"],
                "answer" => "De magie van Technolab in stand houden is essentieel! ✨ We verwachten dat je je als echte Technolabber gedraagt.",
                "suggestions" => [
                    "Wat is Technolab?",
                    "Hoe werkt dit?",
                    "Wat kan ik vragen?"
                ]
            ],

            // ── APP GROEP ─────────────────────────────────────────────────────
            "appgroep" => [
                "keywords" => ["appgroep", "signal", "app groep"],
                "answer" => "We gebruiken Signal voor werk gerelateerde dingen. 📱 Geen WhatsApp!",
                "suggestions" => [
                    "Hoe meld ik me ziek via Signal?",
                    "Hoe meld ik me aan voor de Signal appgroep?",
                    "Welke app gebruiken we voor werk?"
                ],
                "sub_topics" => [
                    "welke" => [
                        "keywords" => ["signal welke", "signal app", "appgroep welke", "whatsapp signal", "welke app gebruiken we voor werk"],
                        "answer" => "We gebruiken Signal voor werk gerelateerde dingen 📱 Geen WhatsApp voor werkzaken!"
                    ],
                    "ziek" => [
                        "keywords" => ["signal ziekmelden", "appgroep ziek", "signal ziek melden", "hoe meld ik me ziek via signal"],
                        "answer" => "Ziekmeldingen moeten ook telefonisch doorgegeven worden aan de dagco. App alleen is niet genoeg! 📞"
                    ],
                    "aanmelden" => [
                        "keywords" => ["signal aanmelden", "appgroep joinen", "signal link", "appgroep aanmelden", "hoe meld ik me aan voor de signal appgroep"],
                        "answer" => "Meld je aan via de link die je van Technolab krijgt 🔗"
                    ],
                ]
            ],

            // ── NOODNUMMER ────────────────────────────────────────────────────
            "noodnummer" => [
                "keywords" => ["noodnummer", "noodcontact", "nood formulier"],
                "answer" => "Vul het noodnummerformulier in zodat Technolab jou in geval van nood kan bereiken. 📋",
                "suggestions" => [
                    "Waar vind ik het noodnummer formulier?",
                    "Wie bel ik in geval van nood?",
                    "Voor wie is het noodnummer formulier?"
                ]
            ],

            // ── E-MAILHANDTEKENING ────────────────────────────────────────────
            "emailhandtekening" => [
                "keywords" => ["emailhandtekening", "email handtekening", "handtekening outlook"],
                "answer" => "Je emailhandtekening kan je aanpassen in Outlook met je Technolab plaatje en links. ✉️",
                "suggestions" => [
                    "Hoe maak ik een emailhandtekening aan?",
                    "Hoe voeg ik een plaatje toe aan mijn emailhandtekening?",
                    "Hoe voeg ik een link toe aan mijn emailhandtekening?"
                ],
                "sub_topics" => [
                    "aanmaken" => [
                        "keywords" => ["emailhandtekening aanmaken", "handtekening maken outlook", "eerste handtekening outlook", "hoe maak ik een emailhandtekening aan"],
                        "answer" => "Voor het aanmaken van je eerste emailhandtekening is er een handleiding beschikbaar. Vraag die op bij Technolab 📖"
                    ],
                    "plaatje" => [
                        "keywords" => ["emailhandtekening plaatje", "handtekening afbeelding", "handtekening logo", "plaatje toevoegen handtekening", "hoe voeg ik een plaatje toe aan mijn emailhandtekening"],
                        "answer" => "Nieuwe plaatjes worden via Teams gedeeld. Kopieer het plaatje, ga in Outlook naar Instellingen → e-mailhandtekening en voeg het toe 🖼️"
                    ],
                    "link" => [
                        "keywords" => ["emailhandtekening link", "handtekening link toevoegen", "klikbare link handtekening", "hoe voeg ik een link toe aan mijn emailhandtekening"],
                        "answer" => "Klik op de afbeelding → 'Koppeling invoegen' → voeg de passende link toe 🔗"
                    ],
                ]
            ],

            // ── HOLACRATIE / WERKOVERLEG ──────────────────────────────────────
            "holacratie" => [
                "keywords" => ["holacratie", "holacratisch", "werkoverleg"],
                "answer" => "Holacratisch werkoverleg is strak vergaderen volgens vaste regels. Elke cirkel heeft wekelijks zo'n overleg. 🔄",
                "suggestions" => [
                    "Wat is holacratie?",
                    "Wie is de facilitator bij holacratie?",
                    "Wat is een holacratie cirkel?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["holacratie wat", "holacratie uitleg", "holacratisch werken wat", "wat is holacratie"],
                        "answer" => "Holacratisch werkoverleg is strak vergaderen volgens vaste regels. Elke cirkel heeft wekelijks zo'n overleg 📅"
                    ],
                    "facilitator" => [
                        "keywords" => ["holacratie facilitator", "holacratie secretaris", "werkoverleg leider", "holacratie leidt", "wie is de facilitator bij holacratie"],
                        "answer" => "De facilitator (gekozen per periode) leidt het overleg. De secretaris zorgt dat taken in de teamsplanner worden vastgelegd ✍️"
                    ],
                    "cirkel" => [
                        "keywords" => ["holacratie cirkel", "cirkel werkoverleg", "cirkel holacratie wat", "wat is een holacratie cirkel"],
                        "answer" => "Een cirkel is een team binnen Technolab. Elke cirkel heeft een eigen werkoverleg en planner 🔵"
                    ],
                ]
            ],

            // ── PLANNER ───────────────────────────────────────────────────────
            "planner" => [
                "keywords" => ["planner", "team agenda", "teamplanner"],
                "answer" => "Elke cirkel heeft een planner die de agenda beheert en veranderingen afstemmt. 📅",
                "suggestions" => [
                    "Hoe werkt de planner?",
                    "Waar vind ik het planner wachtwoord?",
                    "Wie staan er in de planner als teamleden?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["planner hoe", "planner werken", "planner agenda hoe", "hoe werkt de planner"],
                        "answer" => "Elke cirkel heeft een planner die de agenda beheert en veranderingen afstemmt 📋"
                    ],
                    "wachtwoord" => [
                        "keywords" => ["planner wachtwoord", "planner vergrendeld", "planner toegang", "waar vind ik het planner wachtwoord"],
                        "answer" => "De agenda is vergrendeld met wachtwoord. Vraag het bij de hoofdplanner in overleg met je rolverdeler 🔒"
                    ],
                    "teamleden" => [
                        "keywords" => ["planner teamleden", "planner team overzicht", "planner werkdagen", "wie staan er in de planner als teamleden"],
                        "answer" => "In de agenda zit een tabblad met alle teamleden, stagiairs en hun werkdagen. Zorg dat jij er ook bij staat! 👥"
                    ],
                ]
            ],

            // ── BUDDY / COACHING ──────────────────────────────────────────────
            "coaching" => [
                "keywords" => ["buddy", "coaching", "coach"],
                "answer" => "Elke medewerker zoekt een buddy om leerdoelen te bespreken. Coaches helpen met persoonlijke uitdagingen. 🤝",
                "suggestions" => [
                    "Wat is het buddy systeem?",
                    "Hoe werkt het coaching traject?",
                    "Wie is mijn coach?"
                ],
                "sub_topics" => [
                    "buddy" => [
                        "keywords" => ["buddy systeem", "buddy leerdoel", "buddy technolab", "buddy wat", "wat is het buddy systeem"],
                        "answer" => "Elke medewerker zoekt een buddy binnen Technolab om eigen leerdoelen te bespreken en te evalueren 🎯"
                    ],
                    "coach" => [
                        "keywords" => ["coaching hoe", "coaching afspraken", "coach traject", "coach persoonlijk", "hoe werkt het coaching traject"],
                        "answer" => "Coaches helpen in een traject van 3-4 afspraken met persoonlijke uitdagingen 💬"
                    ],
                    "wie" => [
                        "keywords" => ["coach wie", "coaching wie", "coach organigram", "coach talentontwikkeling", "wie is mijn coach"],
                        "answer" => "Cirkel Talentontwikkeling verzorgt Coaching en trainingen. Zie het organigram voor wie op dit moment coach is 🗂️"
                    ],
                ]
            ],

            // ── VERTROUWENSPERSOON ────────────────────────────────────────────
            "vertrouwenspersoon" => [
                "keywords" => ["vertrouwenspersoon", "vertrouwelijk bespreken"],
                "answer" => "Heb je iets vertrouwelijks te bespreken? Ga naar onze vertrouwenspersoon! 🔒",
                "suggestions" => [
                    "Wie is de vertrouwenspersoon?",
                ],
                "sub_topics" => [
                    "wie" => [
                        "keywords" => ["wie is de vertouwenspersoon"],
                        "answer" => "Maartje Kapteijn is onze vertrouwenspersoon."
                    ]
                ]
            ],

            // ── BUS RIJDEN ────────────────────────────────────────────────────
            "bus" => [
                "keywords" => ["bus", "bus rijden", "bus reserveren"],
                "answer" => "Heb je een rijbewijs? Dan moet je eerst een proefrit doen. Daarna mag je ermee rijden! 🚐",
                "suggestions" => [
                    "Hoe reserveer ik de bus?",
                    "Wat zijn de regels voor het rijden met de bus?"
                ],
                "sub_topics" => [
                    "rijden" => [
                        "keywords" => ["bus rijden hoe", "bus proefrit", "bus rijbewijs", "bus mag ik rijden", "wat zijn de regels voor het rijden met de bus"],
                        "answer" => "Heb je een rijbewijs? Dan moet je eerst een proefrit met de Technolab bus doen. Pas daarna mag je ermee rijden 🚗"
                    ],
                    "reserveren" => [
                        "keywords" => ["bus reserveren", "bus boeken", "bus dagco wiki", "fiets reserveren", "hoe reserveer ik de bus", "kan ik ook een fiets reserveren"],
                        "answer" => "Reserveer via de Dagco Wiki! Dit geldt ook voor fietsen! 📅"
                    ],
                ]
            ],

            // ── BOEKHOUDING / INKOPEN ─────────────────────────────────────────
            "boekhouding" => [
                "keywords" => ["boekhouding", "inkopen", "bonnetje", "declareren", "pinpas technolab"],
                "answer" => "Bij Gamma of Plus koop je met je Technolab pasje. Bonnetje inleveren in de kast in de Groei! 🧾",
                "suggestions" => [
                    "Hoe betaal ik zelf iets voor boekhouding?",
                    "Hoe gebruik ik de pinpas voor boekhouding?",
                    "Hoe bestel ik iets online via boekhouding?"
                ],
                "sub_topics" => [
                    "gamma_plus" => [
                        "keywords" => ["boekhouding gamma", "boekhouding plus", "gamma pasje", "plus pasje"],
                        "answer" => "Bij de Gamma of Plus koop je met je Technolab pasje 🪪 Neem bij de Gamma ook de Gamma-pas mee! Bonnetje in kast in de Groei!"
                    ],
                    "voorschieten" => [
                        "keywords" => ["boekhouding zelf betalen", "boekhouding voorschieten", "boekhouding terugkrijgen", "declareren hoe", "hoe betaal ik zelf iets voor boekhouding"],
                        "answer" => "Stuur foto van bonnetje + rekeninggegevens naar boekhouding@technolableiden.nl ✉️ Vraag altijd akkoord van producteigenaar!"
                    ],
                    "pinpas" => [
                        "keywords" => ["boekhouding pinpas", "pinpas technolab gebruiken", "pinpas code", "hoe gebruik ik de pinpas voor boekhouding"],
                        "answer" => "Foto van bonnetje naar boekhouding en origineel in kast in Groei 🧾 Vraag waar pinpas en code zijn!"
                    ],
                    "online" => [
                        "keywords" => ["boekhouding online", "online bestellen boekhouding", "internet aankoop boekhouding", "hoe bestel ik iets online via boekhouding"],
                        "answer" => "Stuur op tijd een link naar boekhouding — liefst met akkoord van producteigenaar 🛒"
                    ],
                    "overig" => [
                        "keywords" => ["boekhouding parkeren", "boekhouding bus wassen", "boekhouding geen bonnetje", "overige kosten boekhouding"],
                        "answer" => "Andere uitgaven zonder bonnetje? Bespreek met boekhouding, we vinden samen een oplossing 🤝"
                    ],
                    "voorraad" => [
                        "keywords" => ["boekhouding voorraad", "voorraad op boekhouding", "toiletpapier boekhouding", "koffie thee voorraad"],
                        "answer" => "Voorraad op raakt? Laat het boekhouding/inkoop weten! 📦"
                    ],
                ]
            ],

            // ── PAPIER HERGEBRUIKEN ───────────────────────────────────────────
            "papier" => [
                "keywords" => ["papier hergebruiken", "papier recyclen", "papier brein", "papier dubbelzijdig"],
                "answer" => "Op Technolab hergebruiken we papier! ♻️ Sorteer papier in het brein: dubbelzijdig of engelszijdig bedrukt.",
                "suggestions" => [
                    "Hoe sorteer ik papier in het brein?",
                    "Wat is het papier brein?",
                    "Hoe gaan we duurzaam om met papier?"
                ]
            ],
        ];
        $this->buildSuggestionMap();
    }

    private function initializeSynonyms(): void
    {
        $this->synonyms = [
            "loon" => ["salaris", "betaling", "uitbetaling", "verdienste", "inkomsten"],
            "betaling" => ["uitbetaling", "loon", "salaris", "geld", "giro"],
            "lessen" => ["les", "workshop", "lesaanbod", "lesprogramma", "les geven", "training"],
            "techniekwijs" => ["techniek", "wetenschap", "electronics", "maker"],
            "programmeren" => ["coderen", "coding", "digitaal", "toekomsttaal"],
            "duurzaamheid" => ["groen", "klimaat", "toekomstkunde", "milieu"],
            "dagco" => ["dagcoordinator", "dagcoördinator", "coordinator"],
            "stage" => ["stagiair", "stageplaats", "stagelopen", "praktijk"],
            "holacratie" => ["werkoverleg", "cirkel", "team overleg", "cirkeloverleg"],
            "wie" => ["welke persoon", "naam", "contact"],
            "wat" => ["welke", "soort", "type"],
            "hoe" => ["op welke manier", "werkwijze", "proces"],
            "waar" => ["locatie", "plek", "plaats", "adres"],
            "wanneer" => ["tijdstip", "moment", "dag", "uur"],
        ];
    }

    private function initializeSemanticGroups(): void
    {
        $this->semanticGroups = [
            "financial" => ["loon", "salaris", "betaling", "geld", "verdienen", "uitbetaling", "pensioen"],
            "education" => ["lessen", "les", "workshop", "techniekwijs", "toekomsttaal", "toekomstkunde", "programmeren"],
            "organization" => ["dagco", "holacratie", "cirkel", "team", "planner", "werkoverleg"],
            "work" => ["stage", "werk", "job", "stagiair", "medewerker", "contract"],
            "rules" => ["huisregels", "gedrag", "regels", "protocol", "richtlijn"],
        ];
    }

    private function buildSuggestionMap(): void
    {
        foreach ($this->intents as $intentName => $intent) {
            if (!isset($intent["suggestions"]) || !isset($intent["sub_topics"])) {
                continue;
            }

            foreach ($intent["suggestions"] as $suggestion) {
                // Find the best matching sub-topic for this suggestion
                $bestMatch = $this->findSubTopicForSuggestion($intentName, $suggestion);
                if ($bestMatch) {
                    $normalizedSuggestion = $this->normalizeMessage($suggestion);
                    $this->suggestionMap[$normalizedSuggestion] = [$intentName, $bestMatch];
                }
            }
        }
    }

    private function findSubTopicForSuggestion(string $intentName, string $suggestion): ?string
    {
        $intent = $this->intents[$intentName];
        $suggestionWords = $this->tokenizeMessage($this->normalizeMessage($suggestion));

        $bestMatch = null;
        $bestScore = 0;

        foreach ($intent["sub_topics"] as $subKey => $subTopic) {
            $keywords = $subTopic["keywords"] ?? [];
            $score = 0;

            foreach ($keywords as $keyword) {
                $keywordScore = $this->calculateWordToKeywordSemanticScore($this->normalizeMessage($suggestion), $suggestionWords, $keyword);
                $score = max($score, $keywordScore);
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $subKey;
            }
        }

        return $bestMatch;
    }

    private function findSuggestionMatch(string $normalizedMessage): ?array
    {
        // Exact match
        if (isset($this->suggestionMap[$normalizedMessage])) {
            return $this->suggestionMap[$normalizedMessage];
        }

        // Fuzzy match with high threshold
        foreach ($this->suggestionMap as $suggestion => $data) {
            $similarity = $this->stringSimilarity($normalizedMessage, $suggestion);
            if ($similarity > 0.85) {
                return $data;
            }
        }

        return null;
    }

    private function stringSimilarity(string $a, string $b): float
    {
        $distance = levenshtein($a, $b);
        $maxLength = max(strlen($a), strlen($b));
        if ($maxLength === 0) {
            return 1.0;
        }
        return 1 - ($distance / $maxLength);
    }

    private function normalizeMessage(string $message): string
    {
        $message = mb_strtolower(trim($message));
        $message = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $message); // strip punctuation
        $message = preg_replace('/\s+/', ' ', $message);               // collapse spaces
        return trim($message);
    }

    private function tokenizeMessage(string $message): array
    {
        return array_filter(explode(' ', $message), fn($w) => strlen($w) >= 2);
    }

    public function respond(string $message): array
    {
        $normalizedMessage = $this->normalizeMessage($message);
        $messageWords = $this->tokenizeMessage($normalizedMessage);

        $this->conversationHistory[] = [
            "message" => $message,
            "normalized" => $normalizedMessage,
            "timestamp" => time()
        ];
        $_SESSION["conversation_history"] = $this->conversationHistory;

        // Check if this message is a known suggestion
        $suggestionMatch = $this->findSuggestionMatch($normalizedMessage);
        if ($suggestionMatch) {
            [$intentName, $subTopicKey] = $suggestionMatch;
            return $this->getResponseForSuggestion($intentName, $subTopicKey, $normalizedMessage);
        }

        // Score ALL intents and sub-topics in a single pass
        $allResults = $this->scoreAllIntents($normalizedMessage, $messageWords);

        if (empty($allResults) || $allResults[0]["score"] < 0.4) {
            return [
                "reply" => $this->defaultResponse(),
                "buttons" => []
            ];
        }

        // Check for ties within TIE_THRESHOLD of the top score
        $topScore = $allResults[0]["score"];
        $tied = array_filter($allResults, fn($r) => ($topScore - $r["score"]) <= self::TIE_THRESHOLD);
        $tied = array_values($tied);

        // Only consider a real tie if there are multiple distinct top-level intents
        $tiedTopLevelIntents = [];
        foreach ($tied as $result) {
            $intentName = $result["intent"];
            if (!isset($tiedTopLevelIntents[$intentName])) {
                $tiedTopLevelIntents[$intentName] = $result;
            }
        }

        if (count($tiedTopLevelIntents) >= 2) {
            return $this->buildTieResponse($tiedTopLevelIntents);
        }

        // Single winner intent
        $winner = $allResults[0];
        $winnerIntent = $winner["intent"];

        // Find the best sub-topic result for this intent
        $bestSubTopic = null;
        foreach ($allResults as $result) {
            if ($result["intent"] === $winnerIntent && $result["subTopic"] !== null) {
                $bestSubTopic = $result;
                break; // already sorted by score, first match is best
            }
        }

        // Use sub-topic if it scored >= 0.85
        $subTopicToUse = null;
        if ($bestSubTopic !== null && $bestSubTopic["score"] >= 0.85) {
            $subTopicToUse = $bestSubTopic["subTopic"];
        }

        return $this->getResponse(
            $winnerIntent,
            $this->intents[$winnerIntent],
            $normalizedMessage,
            $subTopicToUse
        );
    }

    /**
     * Score ALL intents AND sub-topics in one unified pass.
     * Returns a flat sorted array of results with intent, subTopic, and score.
     * FIX: eliminates double-scoring sub-topics and correctly uses max-keyword scoring.
     */
    private function scoreAllIntents(string $normalizedMessage, array $messageWords): array
    {
        $results = [];

        foreach ($this->intents as $intentName => $intent) {
            // Score the top-level intent
            $intentScore = $this->calculateIntentSemanticScore($normalizedMessage, $messageWords, $intent);
            $results[] = [
                "intent"   => $intentName,
                "subTopic" => null,
                "score"    => $intentScore,
            ];

            // Score sub-topics in the same pass
            if (isset($intent["sub_topics"])) {
                foreach ($intent["sub_topics"] as $subKey => $subTopic) {
                    $subScore = $this->calculateIntentSemanticScore($normalizedMessage, $messageWords, $subTopic);
                    $results[] = [
                        "intent"   => $intentName,
                        "subTopic" => $subKey,
                        "score"    => $subScore,
                    ];
                }
            }
        }

        // Sort highest score first
        usort($results, fn($a, $b) => $b["score"] <=> $a["score"]);

        return $results;
    }

    /**
     * FIX: Use MAX keyword score instead of average × ratio.
     * A single strong keyword match is enough to identify an intent.
     * Bonus is added when multiple keywords match, but the base is the best single match.
     */
    private function calculateIntentSemanticScore(string $normalizedMessage, array $messageWords, array $intentData): float
    {
        $keywords = $intentData["keywords"] ?? [];
        if (empty($keywords)) {
            return 0.0;
        }

        $maxScore = 0.0;
        $matchCount = 0;

        foreach ($keywords as $keyword) {
            $keywordScore = $this->calculateWordToKeywordSemanticScore($normalizedMessage, $messageWords, $keyword);

            if ($keywordScore > $maxScore) {
                $maxScore = $keywordScore;
            }

            if ($keywordScore > 0.3) {
                $matchCount++;
            }
        }

        // Base score = best single keyword match
        // Small bonus for each additional matching keyword (up to +0.15 total)
        $multiMatchBonus = min(($matchCount - 1) * 0.05, 0.15);

        return min($maxScore + $multiMatchBonus, 1.0);
    }

    private function calculateWordToKeywordSemanticScore(string $normalizedMessage, array $messageWords, string $keyword): float
    {
        // Strategy 1: Exact phrase match in full message
        if (str_contains($normalizedMessage, $keyword)) {
            return 1.0;
        }

        $bestScore = 0.0;

        foreach ($messageWords as $word) {
            // Direct match
            if ($word === $keyword) {
                return 1.0;
            }

            $bestScore = max($bestScore, $this->fuzzyWordScore($word, $keyword));
            $bestScore = max($bestScore, $this->synonymWordScore($word, $keyword));
            $bestScore = max($bestScore, $this->semanticGroupScore($word, $keyword));
        }

        return $bestScore;
    }

    private function fuzzyWordScore(string $word, string $keyword): float
    {
        if (strlen($word) < 2 || strlen($keyword) < 2) {
            return 0;
        }

        if (abs(strlen($word) - strlen($keyword)) > 3) {
            return 0;
        }

        $distance = levenshtein($word, $keyword);
        $maxLength = max(strlen($word), strlen($keyword));

        if ($maxLength === 0) {
            return 0;
        }

        $similarity = 1 - ($distance / $maxLength);
        return ($similarity >= 0.75) ? $similarity * 0.85 : 0;
    }

    private function synonymWordScore(string $word, string $keyword): float
    {
        if (isset($this->synonyms[$keyword]) && in_array($word, $this->synonyms[$keyword])) {
            return 0.8;
        }
        if (isset($this->synonyms[$word]) && in_array($keyword, $this->synonyms[$word])) {
            return 0.8;
        }
        return 0;
    }

    private function semanticGroupScore(string $word, string $keyword): float
    {
        foreach ($this->semanticGroups as $groupWords) {
            if (in_array($word, $groupWords) && in_array($keyword, $groupWords)) {
                return 0.65;
            }
        }
        return 0;
    }

    /**
     * Build a tie-response: inform the user and show one button per tied intent.
     */
    private function buildTieResponse(array $tiedIntents): array
    {
        $buttons = [];

        foreach ($tiedIntents as $intentName => $result) {
            $intent = $this->intents[$intentName];

            // Use the first suggestion as button label, or fall back to the intent name
            $label = $intent["suggestions"][0] ?? ucfirst($intentName);

            $buttons[] = [
                "label" => $label,
                "value" => $label,
            ];
        }

        return [
            "reply"   => "Ik weet het niet zeker 🤔 Bedoel je één van deze onderwerpen?",
            "buttons" => array_slice($buttons, 0, 4), // max 4 buttons
        ];
    }

    private function getResponseForSuggestion(string $intentName, string $subTopicKey, string $normalizedClickedSuggestion): array
    {
        $intent = $this->intents[$intentName];
        $subTopic = $intent["sub_topics"][$subTopicKey] ?? null;

        if (!$subTopic) {
            return ["reply" => "Could not find that answer", "buttons" => []];
        }

        $response = [
            "reply" => $subTopic["answer"],
            "buttons" => []
        ];

        if (isset($subTopic["image"])) {
            $response["image"] = $subTopic["image"];
        }

        // Include other suggestions minus the one clicked
        if (isset($intent["suggestions"])) {
            $suggestionCount = 0;
            foreach ($intent["suggestions"] as $suggestion) {
                $normalizedSuggestion = $this->normalizeMessage($suggestion);

                // Skip the clicked suggestion
                if ($normalizedSuggestion === $normalizedClickedSuggestion) {
                    continue;
                }

                if ($suggestionCount < 3) {
                    $response["buttons"][] = [
                        "label" => $suggestion,
                        "value" => $suggestion
                    ];
                    $suggestionCount++;
                }
            }
        }

        return $response;
    }

    private function getResponse(
        string $intentName,
        array $intent,
        string $message,
        ?string $subTopic = null
    ): array {

        // FIX: Greeting handler — return the matched keyword as-is instead of splitting on space
        if ($intentName === "hallo") {
            $greeting = $this->extractGreeting($message, $intent["keywords"]);
            $answer = ucfirst($greeting) . "! Fijn je te ontmoeten! Waar kan ik je mee helpen?";

            $buttons = [];
            foreach (array_slice($intent["suggestions"] ?? [], 0, 3) as $suggestion) {
                $buttons[] = ["label" => $suggestion, "value" => $suggestion];
            }
            return ["reply" => $answer, "buttons" => $buttons];
        }

        // Specific sub-topic response
        if ($subTopic && isset($intent["sub_topics"][$subTopic])) {
            $subTopicData = $intent["sub_topics"][$subTopic];
            $response = ["reply" => $subTopicData["answer"], "buttons" => []];
            if (isset($subTopicData["image"])) {
                $response["image"] = $subTopicData["image"];
            }
            return $response;
        }

        // Main intent response with suggestion buttons
        $buttons = [];
        foreach (array_slice($intent["suggestions"] ?? [], 0, 3) as $suggestion) {
            $buttons[] = ["label" => $suggestion, "value" => $suggestion];
        }

        $response = ["reply" => $intent["answer"], "buttons" => $buttons];

        if (isset($intent["image"])) {
            $response["image"] = $intent["image"];
        }

        return $response;
    }

    /**
     * FIX: Return the full matched keyword phrase, not just the first word.
     * "goede morgen" now correctly returns "goede morgen" instead of "goede".
     */
    private function extractGreeting(string $message, array $keywords): string
    {
        $normalizedMessage = $this->normalizeMessage($message);
        $messageWords = $this->tokenizeMessage($normalizedMessage);

        foreach ($keywords as $keyword) {
            // Check full phrase first
            if (str_contains($normalizedMessage, $keyword)) {
                return $keyword;
            }

            // Single-word keywords: fuzzy match against message words
            if (!str_contains($keyword, ' ')) {
                foreach ($messageWords as $word) {
                    if ($this->fuzzyWordScore($word, $keyword) > 0.8 || $word === $keyword) {
                        return $keyword;
                    }
                }
            }
        }

        return "hallo";
    }

    private function defaultResponse(): string
    {
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
$message = strip_tags(trim($message));
$message = mb_substr($message, 0, 500);
if ($message === "") {
    echo json_encode(["reply" => "", "buttons" => []]);
    exit;
}

$bot = new TechnoBot();
$response = $bot->respond($message);
echo json_encode($response);
