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
    private array $suggestionMap = [];

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
                    "Wat is fika?",
                    "Hoe betaal ik zelf iets voor boekhouding?"
                ]
            ],


            // ── TECHNOLAB ─────────────────────────────────────────────────────
            "technolab" => [
                "keywords" => [
                    "technolab",
                    "wat doen jullie",
                    "hoe groot is technolab",
                    "wat voor projecten doen jullie",
                    "wat kan ik vragen"
                ],
                "answer" => "Technolab Leiden is een leerwerkbedrijf met passie voor onderwijs, techniek, wetenschap en talentontwikkeling. 🌟 Meer weten? Bezoek <a href='https://www.technolableiden.nl/' target='_blank'>technolableiden.nl</a>",
                "suggestions" => [
                    "Wat is Technolab?",
                    "hoe werkt technolab?",
                    "Wat voor projecten doen jullie?"
                ],
                "sub_topics" => [
                    "groot" => [
                        "keywords" => ["technolab groot", "hoeveel scholen technolab", "hoe groot is technolab"],
                        "answer" => "Ruim 36.000 leerlingen, meer dan 50 scholen en circa 100 bedrijven en organisaties doen elk jaar mee aan de lessen en projecten van Technolab! 🏫 <a href='https://www.technolableiden.nl/' target='_blank'>technolableiden.nl</a>",
                    ],
                    "wie" => [
                        "keywords" => ["wie is technolab", "wie zijn wij technolab", "wat is technolab"],
                        "answer" => "Bij Technolab verbinden we onderwijs, techniek en talentontwikkeling. Samen met scholen en bedrijven laten we kinderen, jongeren én medewerkers ontdekken: wie ben ik, wat kan ik, wat wil ik? We maken ze enthousiast voor natuur en techniek: de toekomst! 🌟 <a href='https://www.technolableiden.nl/' target='_blank'>technolableiden.nl</a>",
                    ],
                    "projecten" => [
                        "keywords" => ["wat doen jullie technolab", "technolab projecten", "wat voor projecten doen jullie"],
                        "answer" => "We organiseren workshops, lessen (zoals TechniekWijs, ToekomstTaal en Toekomstkunde), POP-UP projectweken, beroepsoriëntatietrajecten (Talent & Toekomst), de Willie Wortel Wedstrijd, de Meesterchallenge en Expeditie Leerkracht. Ook bieden we stages en leerwerkplekken voor mbo-, hbo- en wo-studenten! 🚀 <a href='https://www.technolableiden.nl/' target='_blank'>technolableiden.nl</a>",
                    ],
                    "hoe_werkt" => [
                        "keywords" => ["hoe werkt technolab intern", "hoe werkt technolab", "hoe werkt dit technolab"],
                        "answer" => "Bij Technolab leer je door te doen! Leerlingen, studenten en professionals werken samen in een creatieve omgeving. We geven lessen op locatie in Leiden én op scholen (POP-UP). Medewerkers werken in cirkels (holacratie) en nemen verantwoordelijkheid voor hun eigen rol. Elke dag zijn er zo'n 20 stagiairs actief aan het werk! 💡",
                    ],
                    "wat_kan_vragen" => [
                        "keywords" => ["wat kan ik vragen aan technobot", "wat kun je beantwoorden", "waarmee kan je helpen technolab"],
                        "answer" => "Je kunt mij vragen over van alles rondom Technolab! Denk aan: Fika, BHV, pasjes, pensioen, MDT, loon, VOG, huisregels, urenregistratie, holacratie, de dagco, lessen en leskisten, stage lopen, en nog veel meer. Probeer het gewoon! 😄",
                    ],
                ]
            ],

            "oke" => [
                "keywords" => [
                    "sorry ik bedoelde het niet",
                    "ik snap het niet",
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
                    "Wie is Anis Hadj Moussa?",
                ],
                "sub_topics" => [
                    "wie" => [
                        "keywords" => ["wie is anis hadj moussa", "wie is anis hadj", "wie is de hadj goat"],
                        "answer" => "",
                        "image" => "Images/Anissss.gif"
                    ],
                ]
            ],

            "big D" => [
                "keywords" => ["big d", "dayaan"],
                "answer" => "",
                "image" => "Images/DiddyD.jpg"
            ],
            "inspiratie_aanmelden" => [
                "keywords" => [
                    "inspiratie aanmelden",
                    "inspiratie",
                    "inspiratie middag",
                    "aanmelden inspiratiemiddag",

                    "welke trajecten zijn er",
                    "welke trajecten",
                    "trajecten",
                    "wat kan ik doen",
                    "mogelijkheden",
                    "opleidingen",
                    "leertrajecten"
                ],
                "answer" => "Wil je ontdekken of werken in de techniek of het onderwijs bij je past? Kom naar onze Inspiratiemiddag of Terugkomdag! 🌟 Meer weten of aanmelden? Bezoek <a href='https://www.technolableiden.nl/' target='_blank'>technolableiden.nl</a>",
                "suggestions" => [
                    "Wanneer is de Inspiratiemiddag?",
                    "Wat is de Terugkomdag?",
                    "Welke trajecten zijn er?"
                ],
                "sub_topics" => [
                    "inspiratiemiddag" => [
                        "keywords" => [
                            "inspiratiemiddag",
                            "inspiratieavond",
                            "wanneer inspiratiemiddag",
                            "speeddaten coaches"
                        ],
                        "answer" => "✨ <b>Inspiratiemiddag & -avond</b><br>Ontdek laagdrempelig wat werken met techniek en onderwijs inhoudt. Inclusief leskennismaking, rondleiding, speeddates met coaches en een netwerkborrel!<br>📅 <b>Komende data:</b><br>• Woensdag 24 juni 2026 | 14:00 – 16:15 (Onderwijs)<br>• Woensdag 15 juli 2026 | 19:00 – 21:15 (Techniek)<br><a href='https://www.technolableiden.nl/' target='_blank'>Meer informatie →</a>",
                    ],
                    "terugkomdag" => [
                        "keywords" => [
                            "terugkomdag",
                            "meesterchallenge terugkomdag",
                            "terugkomdag technolab"
                        ],
                        "answer" => "🔄 <b>Terugkomdag</b><br>Ben je een (oud-)deelnemer of betrokken bij de Meesterchallenge? Kom gezellig netwerken en ervaringen uitwisselen tijdens de terugkomdag.<br>📅 <b>Komende Terugkomdag:</b><br>Woensdag 20 mei 2026 | 14:00 – 16:15<br><a href='https://www.technolableiden.nl/' target='_blank'>Meer informatie →</a>",
                    ],
                    "ontdek_onderwijs" => [
                        "keywords" => [
                            "ontdek onderwijs",
                            "expeditie leerkracht",
                            "meesterchallenge",
                            "trajecten onderwijs",
                            "onderwijs ontdekken"
                        ],
                        "answer" => "🍎 <b>Ontdek het onderwijs</b><br>Sta je op het punt om een nieuwe weg in te slaan met meer impact? Ervaar via twee inspirerende trajecten of het onderwijs bij je past: <i>Expeditie Leerkracht</i> of de <i>Meesterchallenge</i>. <a href='https://www.technolableiden.nl/' target='_blank'>Meer informatie →</a>",
                    ],
                    "ontdek_techniek" => [
                        "keywords" => [
                            "ontdek techniek",
                            "expeditie techniek",
                            "techniek en toekomst",
                            "techniek trajecten"
                        ],
                        "answer" => "🛠️ <b>Ontdek de techniek</b><br>Wil je met je handen werken maar weet je nog niet welke richting? Start met onze 2-daagse Expeditie Techniek en groei door via een challenge of praktijkgerichte stage bij Techniek & Toekomst. <a href='https://www.technolableiden.nl/' target='_blank'>Meer informatie →</a>",
                    ],
                ]
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
                        "keywords" => ["dagco wat doet", "wat doet de dagco", "dagco taken"],
                        "answer" => "De dagco zorgt dat Technolab op tijd open is, zet koffie en thee klaar, opent de dag in de kring met een energiser en houdt gedurende de dag bij wie het pand verlaat of terugkomt. Ook regel je vervoer via de dagco! 🚐☕",
                    ],
                    "bereiken" => [
                        "keywords" => ["dagco bellen", "hoe bereik ik de dagco", "dagco nummer", "dagco telefoon"],
                        "answer" => "De dagco is bereikbaar op 071-5191324. Bel bij ziekte of verhindering tussen 08:10 en 08:25 uur! 📞",
                    ],
                    "wie" => [
                        "keywords" => ["wie is de dagco vandaag", "dagco vandaag wie"],
                        "answer" => "Elke dag is er iemand uit een ander team dagco. Kijk op de planner of vraag het aan een collega wie het vandaag is! 👀",
                    ],
                    "sleutel" => [
                        "keywords" => ["dagco sleutel", "dagco alarm openen"],
                        "answer" => "Als dagco krijg je een sleutel van Technolab en wordt uitgelegd hoe het alarm werkt 🔑",
                    ],
                ]
            ],

            // ── PROJECTDAG ────────────────────────────────────────────────────
            "projectdag" => [
                "keywords" => [
                    "projectdag",
                    "project dag",
                    "botsende bots",
                    "groene daken",
                    "mens en robot",
                    "ontwerp je attractie",
                    "duurzaam huis",
                    "avontuurlijke architecten",
                    "welke projecten zijn er",
                    "projecten voor leerlingen",
                    "basisschool projecten"
                ],
                "answer" => "Technolab heeft zes projectdagen voor verschillende groepen! Klik op een project voor meer informatie. 🔧🌱",
                "suggestions" => [
                    "Botsende Bots",
                    "Groene Daken",
                    "Mens en Robot",
                    "Ontwerp je Attractie",
                    "Duurzaam Huis",
                    "Avontuurlijke Architecten"
                ],
                "sub_topics" => [
                    "botsende_bots" => [
                        "keywords" => [
                            "botsende bots",
                            "botsende bots groep 8",
                            "bots programmeren project",
                            "robotica groep 8"
                        ],
                        "answer" => "🤖 <b>Botsende Bots</b> (Groep 8)<br><br><b>Contactpersoon TK:</b> Julian &nbsp;|&nbsp; <b>Opdrachtgever:</b> Melissa<br><br><b>Samenvatting:</b> Leerlingen lossen ontwerpproblemen op, leren de basis van programmeren en werken samen in drietallen.<br><br><b>Lesdoelen:</b><br>• Ontwerpproblemen oplossen door creativiteit en doorzettingsvermogen<br>• De basis leren van programmeren<br>• Samenwerken in drietallen<br>• Leren werken met de ontwerpcyclus: testen en verbeteren",
                    ],
                    "groene_daken" => [
                        "keywords" => [
                            "groene daken",
                            "groene daken groep 7",
                            "zonnepaneel project groep",
                            "groen dak bouwen school"
                        ],
                        "answer" => "🌿 <b>Groene Daken</b> (Groep 7)<br><br><b>Contactpersoon TK:</b> Roos &nbsp;|&nbsp; <b>Contactpersoon KIEM:</b> Alide / Johan (Solar Groep) &nbsp;|&nbsp; <b>Opdrachtgever:</b> Johan / Solar Groep<br><br><b>Samenvatting:</b> Leerlingen ontwerpen en maken hun eigen groene dak, maken kennis met installatietechniek, doen onderzoek naar de optimale stand van een zonnepaneel, het beste materiaal voor een zonneboiler en geschikte planten voor een groen dak.<br><br><b>Lesdoelen:</b><br>• Ontwerpproblemen oplossen door creativiteit en doorzettingsvermogen<br>• Samenwerken in drietallen<br>• Leren zelf een praktisch onderzoek te doen",
                    ],
                    "mens_en_robot" => [
                        "keywords" => [
                            "mens en robot",
                            "mens robot groep 6",
                            "skelet bouwen project",
                            "hartfunctie onderzoek groep"
                        ],
                        "answer" => "🦾 <b>Mens en Robot</b> (Groep 6)<br><br><b>Contactpersoon TK:</b> Eline / Roos<br><br><b>Samenvatting:</b> Leerlingen bouwen een menselijk skelet met aandacht voor vorm en functie, doen onderzoek naar hartfunctie, bewegen en verhoudingen, en bouwen een robot die voor de mens van nut kan zijn.<br><br><b>Lesdoelen:</b><br>• Ontwerpproblemen oplossen door creativiteit en doorzettingsvermogen<br>• Samenwerken in drietallen<br>• Leren zelf een praktisch onderzoek te doen",
                    ],
                    "ontwerp_attractie" => [
                        "keywords" => [
                            "ontwerp je attractie",
                            "attractie bouwen groep 5",
                            "pretpark ontwerpen schaal",
                            "attractie programmeren groep"
                        ],
                        "answer" => "🎢 <b>Ontwerp je Attractie</b> (Groep 5)<br><br><b>Contactpersoon TK:</b> Robert / Celine &nbsp;|&nbsp; <b>Contactpersoon KIEM:</b> Coen (Joravision) &nbsp;|&nbsp; <b>Opdrachtgever:</b> Coen<br><br><b>Samenvatting:</b> Leerlingen ontwerpen en bouwen hun eigen attractie op schaal als onderdeel van een nieuw pretpark. De groepen stemmen met elkaar af om samen een zo gevarieerd mogelijk pretpark te maken.<br><br><b>Lesdoelen:</b><br>• Ontwerpproblemen oplossen door creativiteit en doorzettingsvermogen<br>• De basis leren van programmeren<br>• Samenwerken in drietallen en afstemmen met andere groepen",
                    ],
                    "duurzaam_huis" => [
                        "keywords" => [
                            "duurzaam huis",
                            "duurzaam huis groep 4",
                            "huis isolatie project groep",
                            "duurzaam bouwen basisschool"
                        ],
                        "answer" => "🏡 <b>Duurzaam Huis</b> (Groep 4)<br><br><b>Contactpersoon TK:</b> Jolien / Roos<br><br><b>Samenvatting:</b> Leerlingen ontwerpen en bouwen een duurzaam huis, doen onderzoek naar isolatie, elektriciteit en verbruik van apparaten.<br><br><b>Lesdoelen:</b><br>• Ontwerpproblemen oplossen door creativiteit en doorzettingsvermogen<br>• Samenwerken in viertallen<br>• Leren zelf een praktisch onderzoek te doen",
                    ],
                    "avontuurlijke_architecten" => [
                        "keywords" => [
                            "avontuurlijke architecten",
                            "architecten groep 3",
                            "pretpark bouwen groep 3",
                            "bruggen bouwen basisschool"
                        ],
                        "answer" => "🏗️ <b>Avontuurlijke Architecten</b> (Groep 3)<br><br><b>Contactpersoon TK:</b> Sanne (Leiden) &nbsp;|&nbsp; <b>Contactpersoon KIEM:</b> Alide<br><br><b>Samenvatting:</b> Leerlingen ontwerpen en bouwen hun eigen pretpark met verschillende constructiematerialen. De groepen stemmen samen af. Denk aan bruggen, omheiningen, wegen en bewegwijzering!<br><br><b>Lesdoelen:</b><br>• Ontwerpproblemen oplossen door creativiteit en doorzettingsvermogen<br>• Samenwerken in drietallen en afstemmen met andere groepen",
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
                        "keywords" => ["leskist inhoud", "wat zitten er in de leskisten", "leskist materiaal"],
                        "answer" => "In de blauwe leskisten zit al het materiaal voor een Technolab les: handleidingen, materialen voor experimenten en opdrachten. Alles wat je nodig hebt voor een goede les zit erin! 🔬🔧",
                    ],
                    "lessen" => [
                        "keywords" => ["leskist lessen", "welke lessen horen bij de leskisten"],
                        "answer" => "De leskisten horen bij de lessen van Technolab, zoals TechniekWijs (wetenschap & techniek), ToekomstTaal (programmeren & mediawijsheid) en Toekomstkunde (duurzaamheid & technologie). 📚",
                    ],
                    "gebruik" => [
                        "keywords" => ["leskist gebruik school", "waar worden leskisten gebruikt"],
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
                        "keywords" => ["stage aanmelden", "stage inschrijven", "stage aanvragen", "hoe meld ik me aan voor een stage"],
                        "answer" => "Aanmelden kan via het formulier op technolableiden.nl/over-technolab/stage-leiden/. Daarna volg je de stappen: aanmelden → inspiratiemiddag → ontdekdag → match & start! 📝",
                    ],
                    "wat_doen" => [
                        "keywords" => ["stage wat doe je", "stage werkzaamheden", "wat kan ik doen tijdens mijn stage"],
                        "answer" => "Tijdens je stage werk je mee aan inspirerend techniekonderwijs voor kinderen en jongeren. Je werkt in een multidisciplinair team, krijgt ruimte voor eigen ideeën en begeleiding gericht op jouw leerdoelen. Elke dag zijn er zo'n 20 stagiairs actief! 💪",
                    ],
                    "richtingen" => [
                        "keywords" => ["stage richtingen opleiding", "voor welke studierichtingen is stage mogelijk"],
                        "answer" => "Technolab zoekt stagiairs uit diverse richtingen, zoals Toegepaste Psychologie, HBO-ICT / Innovative Development, Media Vormgeven en MLO. De mix van achtergronden zorgt voor een inspirerende leeromgeving! 🎨💻",
                    ],
                    "contact" => [
                        "keywords" => ["stage contact email vragen bellen"],
                        "answer" => "Vragen over je stage? Bel 071-5191324 of mail naar stage@technolableiden.nl. Let op: tijdens schoolvakanties wordt mail minder vaak gelezen 📧",
                    ],
                ]
            ],

            // ── LESSEN ────────────────────────────────────────────────────────
            "lessen" => [
                "keywords" => [
                    "les",
                    "lessen",
                    "lesaanbod",
                    "lesprogramma",
                    "workshop",
                    "workshops",
                    "techniekwijs",
                    "toekomsttaal",
                    "popup",
                    "willie wortel",
                    "pop up les",
                    "welke lessen zijn er",
                    "alle lessen",
                    "lessen overzicht"
                ],
                "answer" => "📚 Klik op een les hieronder voor meer info! Bekijk alles op <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>technolableiden.nl/toekomstkunde</a>",
                "suggestions" => [
                    "Robot Ontdeklab",
                    "Kleine Muis",
                    "Experimenten met elementen",
                    "Electriciteit",
                    "Avontuurlijke Architecten",
                    "Praten met apparaten",
                    "Zintuigen",
                    "Landbouwspel",
                    "Duurzaam huis",
                    "Draadkunst",
                    "Space Jam",
                    "Dierenavontuur",
                    "Schatten van de Aarde",
                    "Ontwerp je attractie",
                    "Bewegende beesten",
                    "Spelen met electriciteit",
                    "Weg met de klere(n)berg",
                    "Energie zuinig",
                    "Boot bouwen",
                    "Bouw je eigen robot",
                    "Bruggen bouwen",
                    "Ruimteraadsels",
                    "Game design",
                    "CSI",
                    "Hackerspace",
                    "DNA",
                    "Mens en Microbiologie",
                    "Weefkunst",
                    "Marsbots",
                    "Groene Daken",
                    "Techniek en Duurzaamheid",
                    "Upcycle"
                ],
                "sub_topics" => [
                    "robot_ontdeklab" => [
                        "keywords" => ["robot ontdeklab", "cubetto", "pixelen dashes", "kleuters programmeren"],
                        "answer" => "🤖 <b>Robot Ontdeklab</b> — Groep 1/2<br>Een eerste verkenning van programmeren. Vier onderdelen: Cubetto, Let's go Code, Pixelen en Dashes. Leerlingen ontdekken spelenderwijs hoe je 'praat met apparaten'. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "kleine_muis" => [
                        "keywords" => ["kleine muis", "kleine muis zoekt een huis", "koude karel warme wilma"],
                        "answer" => "🐭 <b>Kleine Muis</b> — Groep 1/2<br>We lezen voor uit 'Kleine Muis zoekt een huis'. Leerlingen onderzoeken hoe dieren en planten op het schoolplein wonen, maken kennis met Koude Karel en Warme Wilma en bedenken waar zij zelf het liefst wonen. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "experimenten_elementen" => [
                        "keywords" => ["experimenten met elementen", "elementen ontdekken vergrootglas"],
                        "answer" => "🔬 <b>Experimenten met elementen</b> — Groep 1/2<br>Leerlingen ontdekken spelenderwijs de verschillende elementen, observeren en benoemen wat ze waarnemen, en bedenken oplossingen for (technische) problemen. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "electriciteit" => [
                        "keywords" => ["electriciteit les", "statische elektriciteit ballon", "plasmabol geleiding"],
                        "answer" => "⚡ <b>Electriciteit</b> — Groep 1/2<br>Kinderen ontdekken statische elektriciteit, leren welke materialen geleiden, ervaren zichtbare stroom met een plasmabol en ontdekken hoe elektriciteit zich gedraagt. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "avontuurlijke_architecten_les" => [
                        "keywords" => ["avontuurlijke architecten les", "pretpark bouwen groepen architecten"],
                        "answer" => "🏗️ <b>Avontuurlijke Architecten</b> — Groep 3<br>De klas bouwt in groepjes een pretpark. Per groepje verzinnen en bouwen de leerlingen zelf hun attractie met verschillende materialen. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "praten_met_apparaten" => [
                        "keywords" => ["praten met apparaten", "cubetto verdieping groep 3"],
                        "answer" => "💬 <b>Praten met apparaten</b> — Groep 3<br>Verdieping op Robot Ontdeklab voor kleuters. Zelfde vier onderdelen (Cubetto, Let's go Code, Pixelen, Dashes) maar nu met extra uitdagingen. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "zintuigen" => [
                        "keywords" => ["zintuigen les", "kermis zintuigen ruiken proeven"],
                        "answer" => "👂 <b>Zintuigen</b> — Groep 3<br>Kinderen ontdekken de kermis met al hun zintuigen via vrolijke opdrachten. De les eindigt met een kermisquiz en een dansfeestje! <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "landbouwspel" => [
                        "keywords" => ["landbouwspel", "boerderij bouwen land water eiwit"],
                        "answer" => "🌾 <b>Landbouwspel</b> — Groep 3<br>Kinderen bouwen hun eigen boerderij en puzzelen hoeveel land en water nodig is om genoeg eiwit te verbouwen. Ze ontdekken welke voedingsmiddelen duurzaam zijn. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "duurzaam_huis_les" => [
                        "keywords" => ["duurzaam huis les", "isolatie waterdichtheid duurzaam bouwen les"],
                        "answer" => "🏡 <b>Duurzaam Huis</b> — Groep 4<br>Leerlingen ontwerpen en bouwen een duurzaam huis, doen onderzoek naar isolatie en waterdichtheid, met zoveel mogelijk hergebruikte materialen. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "draadkunst" => [
                        "keywords" => ["draadkunst", "dier ontwerpen woestijn draad"],
                        "answer" => "🎨 <b>Draadkunst</b> — Groep 4<br>Leerlingen ontwerpen een dier dat kan overleven op een aangegeven locatie. Ze maken eerst een 2D-ontwerp en bouwen dit in 3D. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "space_jam" => [
                        "keywords" => ["space jam les", "makey makey stroomkring instrument"],
                        "answer" => "🚀 <b>Space Jam</b> — Groep 4<br>Leerlingen leren wat een stroomkring is, onderzoeken geleiding en bouwen een papieren/kartonnen instrument met Makey Makey. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "dierenavontuur" => [
                        "keywords" => ["dierenavontuur", "scratch junior dieren programmeren"],
                        "answer" => "🦁 <b>Dierenavontuur</b> — Groep 4<br>Leerlingen ontdekken de basis van programmeren via een dierenavontuur in Scratch Junior. Ze bouwen een digitaal verhaal met bewegende dieren en geluid. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "schatten_aarde" => [
                        "keywords" => ["schatten van de aarde", "aardplaten bodemdieren zaden"],
                        "answer" => "🌍 <b>Schatten van de Aarde</b> — Groep 4<br>Leerlingen ontdekken hoe de planeet werkt: bergen, kringloop van steen, bodemdieren en verspreiding van zaden. Ze zaaien hun eigen boontje! <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "ontwerp_attractie_les" => [
                        "keywords" => ["ontwerp je attractie les", "kermisattractie lego wedo programmeren"],
                        "answer" => "🎢 <b>Ontwerp je attractie</b> — Groep 5, VO, MBO<br>Leerlingen ontwerpen hun eigen kermisattractie, bouwen deze met technisch Lego en brengen deze tot leven met Lego WeDo. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "bewegende_beesten" => [
                        "keywords" => ["bewegende beesten", "byor zoob robot dier bouwen"],
                        "answer" => "🐾 <b>Bewegende beesten</b> — Groep 5<br>Met BYOR (Build Your Own Robot) en Zoob ontwerpen en bouwen leerlingen 3D bewegende dieren, rekening houdend met de leefomgeving. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "spelen_electriciteit" => [
                        "keywords" => ["spelen met electriciteit", "bibberspiraal stroomkring electrospel"],
                        "answer" => "⚡ <b>Spelen met electriciteit</b> — Groep 5<br>Leerlingen ontgevallen elektrische schakelingen: een bibberspiraal, stroomkringopdrachten en een electrospel, via plan-do-check. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "klerenberg" => [
                        "keywords" => ["weg met de klerenberg", "naaimachine tasje textiel duurzaamheid"],
                        "answer" => "👗 <b>Weg met de klere(n)berg</b> — Groep 6<br>Leerlingen maken kennis met duurzaamheid en textiel. Ze leren over circulaire textielverwerking en maken hun eigen tasje van hergebruikt materiaal. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "energie_zuinig" => [
                        "keywords" => ["energie zuinig", "lamp aansluiten water opwarmen zon"],
                        "answer" => "💡 <b>Energie zuinig</b> — Groep 6<br>Twee onderdelen: 'fit de lamp' (lamp aansluiten op stekker) en onderzoek naar water opwarmen met kunstmatige zon. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "boot_bouwen" => [
                        "keywords" => ["boot bouwen", "schip ontwerpen drijven lading"],
                        "answer" => "⛵ <b>Boot bouwen</b> — Groep 6<br>Leerlingen ontwerpen, bouwen en testen een schip: passend bij een thema, max 30×15cm, blijft drijven, draagt 0,5 kg lading. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "bouw_eigen_robot" => [
                        "keywords" => ["bouw je eigen robot", "byor robot sensoren moederbord restmateriaal"],
                        "answer" => "🤖 <b>Bouw je eigen robot</b> — Groep 6, VO, MBO<br>Leerlingen maken kennis met robotica via sensoren en actoren en bouwen een robot van restmaterialen (karton, papier, plastic). <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "bruggen_bouwen" => [
                        "keywords" => ["bruggen bouwen les", "brug constructie ontwerpen groep 7"],
                        "answer" => "🌉 <b>Bruggen bouwen</b> — Groep 7<br>Leerlingen leren verschillende brugvormen en constructies kennen. In groepjes ontwerpen, testen en bouwen ze hun eigen brug. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "ruimteraadsels" => [
                        "keywords" => ["ruimteraadsels microbit", "astronaut radiosignalen cluedo microbit"],
                        "answer" => "🚀 <b>Ruimteraadsels (Micro:bit)</b> — Groep 7, VO, MBO<br>Radiosignalen van een onbekende astronaut zijn binnengekomen. Via een cluedo-achtige aanpak ontdekken leerlingen de identiteit van de astronaut met de Micro:bit. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "game_design" => [
                        "keywords" => ["game design les", "scratch makey mario spel ontwerpen"],
                        "answer" => "🎮 <b>Game design</b> — Groep 7, VO, MBO<br>Leerlingen bedenken zelf een spel dat ze verlevendigen met Scratch en verbinden via MakeyMakey met een laptop. Denk aan een eigen Mario-level! <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "csi" => [
                        "keywords" => ["csi les", "misdaad onderzoek bodem vingerafdrukken"],
                        "answer" => "🔍 <b>CSI</b> — Groep 7, VO, MBO<br>Leerlingen onderzoeken een fictieve misdaad met proefjes rond bodem, haar, vingerafdrukken en poeders. Ze presenteren hun bevindingen en bepalen samen wie de dader is. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "hackerspace" => [
                        "keywords" => ["hackerspace les", "microbit hack professor curiosa geheugen"],
                        "answer" => "💻 <b>Hackerspace</b> — Groep 8, VO, MBO<br>Leerlingen wanen zich in een wereld waar een medicijn tegen geheugenverlies is gehackt. Ze ontdekken hoe de Micro:bit werkt en hoe de hack heeft kunnen plaatsvinden. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "dna" => [
                        "keywords" => ["dna les", "dna kralenketting wangslijmcel mini me"],
                        "answer" => "🧬 <b>DNA</b> — Groep 8, VO<br>Leerlingen ontdekken hoe DNA informatie opslaat. Ze bouwen een mini-me en skelet, maken een DNA-kralenketting og isoleren hun eigen DNA uit wangslijmcel. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "mens_microbiologie" => [
                        "keywords" => ["mens microbiologie", "botten organen microbioom microscopen"],
                        "answer" => "🔬 <b>Mens & Microbiologie</b> — Groep 8<br>Hoe zit ons lichaam er van binnen uit? Leerlingen onderzoeken botten, organen, gezondheid en het microbioom via proefjes en microscopen. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "weefkunst" => [
                        "keywords" => ["weefkunst", "weefraam weven inslagdraden kettingdraden"],
                        "answer" => "🧵 <b>Weefkunst</b> — Groep 8<br>Leerlingen maken kennis met een eeuwenoude weefmethode. Ze zetten zelf een klein weefraam in elkaar en weven daarop een lapje. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "marsbots" => [
                        "keywords" => ["marsbots", "mars autobot makecode microbit programmeren"],
                        "answer" => "🔴 <b>Marsbots</b> — Groep 8, VO, MBO<br>Leerlingen bouwen en programmeren een autobot voor Mars-expeditie via de MakeCode editor. Ze leren stroomkringen, programmeren en samenwerken. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "groene_daken_les" => [
                        "keywords" => ["groene daken les", "zonnepaneel zonneboiler installatietechniek les"],
                        "answer" => "🌿 <b>Groene Daken</b> — PO, VO<br>Leerlingen ontwerpen een groen dak, maken kennis met installatietechniek en doen onderzoek naar zonnepanelen, zonneboilers en planten. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "techniek_duurzaamheid_les" => [
                        "keywords" => ["techniek en duurzaamheid les", "ledlamp isolatie zonnepaneel stad ontwerpen"],
                        "answer" => "🏙️ <b>Techniek en Duurzaamheid</b> — VO, MBO<br>9-delige lessenreeks: ledlamp aansluiten, isolatie, zonnepaneel, energievoorziening en het ontwerpen van een duurzame online stad. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                    "upcycle" => [
                        "keywords" => ["upcycle les", "afval hergebruik product ontwerpen pitchen"],
                        "answer" => "♻️ <b>Upcycle</b> — VO, MBO<br>Afgedankte producten krijgen een nieuw leven. Leerlingen verdiepen zich in afvalstromen en maken hun eigen Upcycle product: van schets tot realisatie en pitch. <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer informatie →</a>"
                    ],
                ],
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
                        "keywords" => ["fika wanneer", "fika welke dag"],
                        "answer" => "Fika is elke woensdag 📅"
                    ],
                    "budget" => [
                        "keywords" => ["fika budget hoeveel geld", "hoeveel is het fika budget"],
                        "answer" => "Er mag rond de €50,- voor Fika worden uitgegeven 💶"
                    ],
                    "boodschappen" => [
                        "keywords" => ["fika boodschappen winkel plus"],
                        "answer" => "Boodschappen worden meestal bij de Plus beneden gedaan 🛒"
                    ],
                    "eten" => [
                        "keywords" => ["fika eten wat koken", "wat eten we bij fika", "fika vegetarisch veganistisch"],
                        "answer" => "We koken veganistisch/vegetarisch 🌱 en consumeren geen alcohol."
                    ],
                    "team" => [
                        "keywords" => ["fika team kookt wie", "wie kookt er bij fika", "fika rad draaien"],
                        "answer" => "Na elke Fika wordt door behulp van een rad een nieuw team gekozen, plus nieuwe ingrediënten🍳",
                        "image" => "Images/Fika.png"
                    ],
                    "verhindering" => [
                        "keywords" => ["fika verhinderd geen tijd vervanging"],
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
                        "keywords" => ["bhv wat betekent uitleg", "wat is bhv", "wat betekent bhv"],
                        "answer" => "BHV staat voor BedrijfsHulpVerlening. BHV'ers zijn aanwezig om te helpen bij noodgevallen 🚨"
                    ],
                    "wie" => [
                        "keywords" => ["bhv wie zijn", "wie zijn de bhvers", "wie zijn de bhv ers", "bhv board knus"],
                        "answer" => "Wie op dit moment BHV'er is, staat op het zwarte board in de knus 🖤",
                        "image" => "Images/bhv.jpg"
                    ],
                    "training" => [
                        "keywords" => ["bhv training wanneer", "wanneer is de bhv training", "bhv cursus opleiding"],
                        "answer" => "Elk jaar volgen medewerkers een BHV training 📚"
                    ],
                    "regels" => [
                        "keywords" => ["bhv regels noodgeval procedure", "wat zijn de bhv regels"],
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
                        "keywords" => ["pasje aanvragen hoe", "hoe vraag ik een pasje aan", "badge aanvragen coordinator"],
                        "answer" => "Je kunt een pasje aanvragen bij de coördinator medewerkers. 🔑",
                        "image" => "Images/Pasje.jpg"
                    ],
                    "liftpas" => [
                        "keywords" => ["liftpas wat is", "liftpas toegang gebouw", "wat is de liftpas"],
                        "answer" => "Sommige medewerkers hebben een liftpas waarmee je de lift kunt gebruiken. Ook hiermee kun je de deur van het gebouw openen."
                    ],
                    "sleutel" => [
                        "keywords" => ["pasje sleutel alarm dagco"],
                        "answer" => "Als dagco krijg je een sleutel van Technolab en wordt uitgelegd hoe het alarm werkt."
                    ],
                    "nacht" => [
                        "keywords" => ["pasje nacht weekend vakantie laat", "nachtwerk pasje regels", "wat zijn de regels voor nachtwerk met een pasje"],
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
                        "keywords" => ["pensioen collectief regeling technolab"],
                        "answer" => "Technolab biedt geen collectieve pensioenregeling 📋"
                    ],
                    "bright" => [
                        "keywords" => ["brightpensioen wat is", "bright pensioen uitleg", "wat is brightpensioen"],
                        "answer" => "BrightPensioen lidmaatschap wordt door Technolab vergoed zolang je een arbeidscontract hebt 💙"
                    ],
                    "aanmelden" => [
                        "keywords" => ["pensioen aanmelden hoe formulier", "hoe meld ik me aan voor pensioen"],
                        "answer" => "Ga naar de coördinator medewerker voor het aanmeldformulier 📝"
                    ],
                    "kosten" => [
                        "keywords" => ["pensioen kosten prijs bedrag", "wat kost het pensioen"],
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
                        "keywords" => ["mdt wat is uitleg betekent"],
                        "answer" => "MDT staat voor Maatschappelijke DienstTijd. Technolab krijgt subsidie voor MDT uren."
                    ],
                    "wie" => [
                        "keywords" => ["mdt voor wie leeftijd jonger 30", "voor wie is mdt"],
                        "answer" => "Ben je jonger dan 30 jaar? Ga naar de MDT coördinator om een formulier in te vullen."
                    ],
                    "uren" => [
                        "keywords" => ["mdt uren registreren wekelijks", "hoe registreer ik mijn mdt uren"],
                        "answer" => "Registreer wekelijks je gewerkte uren. Uren ophopen of compenseren is niet de bedoeling! ⏱️"
                    ],
                ]
            ],

            // ── LOONVERKLARING ────────────────────────────────────────────────
            "loon" => [
                "keywords" => ["loon", "loonverklaring", "salaris", "loonstrook", "uitbetaling"],
                "answer" => "Je loon wordt via een boekhoudingsbureau betaald. Je hebt een loonverklaring én ID kopie nodig. 💳",
                "suggestions" => [
                    "Loon betaling?",
                    "Wat heb ik nodig voor mijn loon?",
                    "Naar wie stuur ik mijn loonverklaring?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["loon hoe wordt betaald boekhoudingsbureau", "hoe wordt mijn loon betaald"],
                        "answer" => "De betaling van je loon gaat via een boekhoudingsbureau."
                    ],
                    "nodig" => [
                        "keywords" => ["loon wat heb ik nodig id loonverklaring", "wat heb ik nodig voor mijn loon"],
                        "answer" => "Voor je uitbetaling is een loonverklaring én een kopie van je ID nodig."
                    ],
                    "sturen" => [
                        "keywords" => ["loonverklaring sturen naar email boekhouding", "naar wie stuur ik mijn loonverklaring"],
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
                        "keywords" => ["vog wat is uitleg betekent"],
                        "answer" => "Een VOG (Verklaring Omtrent Gedrag) is verplicht om in het onderwijs te werken 🏫"
                    ],
                    "aanvragen" => [
                        "keywords" => ["vog aanvragen wie vraagt", "wie vraagt de vog aan technolab"],
                        "answer" => "De VOG wordt voor jou aangevraagd door Technolab 👍"
                    ],
                    "ontvangen" => [
                        "keywords" => ["vog ontvangen doorsturen coordinator", "wat doe ik als ik mijn vog ontvang", "naar wie stuur ik mijn vog"],
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
                        "keywords" => ["huisregels beginnen tijden aanwezig"],
                        "answer" => "Zorg dat je rond 8:15 uur binnen bent, dan starten we samen om 8:30 uur 🕗"
                    ],
                    "ziek" => [
                        "keywords" => ["huisregels ziek melden bellen", "wat doe ik als ik ziek ben volgens de huisregels"],
                        "answer" => "Bel tussen 8:10 en 8:25 uur naar de dagco: 071-5191324 en zeg het je stagebegeleider 📞"
                    ],
                    "gedrag" => [
                        "keywords" => ["huisregels gedrag verboden mag niet", "wat mag niet volgens de huisregels"],
                        "answer" => "Geen kauwgom, telefoon in tas, geen pet in de les, privé blijft privé. 🙅"
                    ],
                    "verlaten" => [
                        "keywords" => ["huisregels pand verlaten weggaan dagco"],
                        "answer" => "Verlaat je het pand? Meld het altijd bij de dagco 🚪"
                    ],
                    "klusjes" => [
                        "keywords" => ["huisregels klusjes opruimen taken", "welke klusjes horen bij de huisregels"],
                        "answer" => "Klusjes zoals opruimen horen erbij — wij zijn 1 team, 1 taak 💪"
                    ],
                ]
            ],

            // ── URENREGISTRATIE ───────────────────────────────────────────────
            "urenregistratie" => [
                "keywords" => ["urenregistratie", "uren schrijven", "uren registreren"],
                "answer" => "Registreer wekelijks je gewerkte uren. Uren ophopen of compenseren is niet de bedoeling! ⏱️",
                "suggestions" => [
                    "Hoe werkt de urenregistratie?",
                    "Mag ik uren opbouwen via urenregistratie?",
                    "Hoe pas ik mijn werkschema aan via urenregistratie?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["urenregistratie hoe werkt wekelijks", "hoe werkt de urenregistratie"],
                        "answer" => "Registreer wekelijks je gewerkte uren. Uren ophopen of compenseren is niet de bedoeling!⏱️"
                    ],
                    "opbouwen" => [
                        "keywords" => ["urenregistratie opbouwen compenseren ophopen", "mag ik uren opbouwen via urenregistratie"],
                        "answer" => "Uren opbouwen of compenseren is niet de bedoeling ❌ Bespreek meer werken met je rolverdeler."
                    ],
                    "schema" => [
                        "keywords" => ["urenregistratie schema aanpassen rolverdeler", "hoe pas ik mijn werkschema aan via urenregistratie"],
                        "answer" => "Wijzigingen in het werkschema worden van tevoren afgesproken met de rolverdeler 📋"
                    ],
                ]
            ],

            // ── TECHNOLABBER ──────────────────────────────────────────────────
            "technolabber" => [
                "keywords" => [
                    "technolabber",
                    "technolab cultuur",
                    "technolab waarden",
                    "technolab gedragscode",
                    "kernwaarden technolab"
                ],
                "answer" => "Technolab Leiden is een leerwerkbedrijf met passie voor onderwijs, techniek, wetenschap en talentontwikkeling. ✨ Als Technolabber draag je die missie actief uit! Meer op <a href='https://www.technolableiden.nl/' target='_blank'>technolableiden.nl</a>",
                "suggestions" => [
                    "Wat zijn de kernwaarden van Technolab?",
                    "Hoe werkt Technolab intern?",
                    "Wat doet Technolab?"
                ],
                "sub_topics" => [
                    "kernwaarden" => [
                        "keywords" => ["kernwaarden van technolab principes", "zijn de kernwaarden van technolab"],
                        "answer" => "Technolab heeft 5 kernwaarden: Samenwerken (duurzame relaties met scholen, bedrijven en overheid), Groeien (continu verbeteren), Bijdragen (handelen met impact voor een duurzame wereld), Leren (talentontwikkeling centraal) en Spelen (samen plezier hebben in werken en leren). 🌟"
                    ],
                    "missie" => [
                        "keywords" => ["missie technolab doel wat doet technolab", "waarom technolab bestaat"],
                        "answer" => "Technolab verbindt onderwijs, techniek en talentontwikkeling. We helpen kinderen, jongeren én medewerkers ontdekken: wie ben ik, wat kan ik, wat wil ik? We enthousiasmeren ze voor natuur en techniek — de toekomst! 🚀 <a href='https://www.technolableiden.nl/' target='_blank'>technolableiden.nl</a>"
                    ],
                    "werkwijze" => [
                        "keywords" => ["hoe werkt technolab intern werkwijze", "holacratie scrum technolab teams"],
                        "answer" => "Technolab werkt in zelfsturende teams op basis van holacratie en scrum. We denken in mogelijkheden en vertalen ideeën snel naar concrete acties. Technolab bruist van energie! ⚡"
                    ],
                    "activiteiten" => [
                        "keywords" => ["activiteiten technolab organiseert programma workshops"],
                        "answer" => "Technolab organiseert workshops, projecten, beroepsoriëntatieweken en leerwerktrajecten. Ook bieden we trainingsprogramma's aan voor medewerkers van scholen en bedrijven, en begeleiden we mbo-, hbo- en wo-studenten bij hun praktijkervaring. 🎓"
                    ],
                    "impact" => [
                        "keywords" => ["technolab impact bereik leerlingen scholen aantallen"],
                        "answer" => "Elk jaar doen ruim 36.000 leerlingen, meer dan 50 scholen en circa 100 bedrijven en organisaties mee aan de lessen en projecten van Technolab. Zo slaan we de brug tussen onderwijs en arbeidsmarkt! 🌍"
                    ],
                ]
            ],

            // ── APP GROEP ─────────────────────────────────────────────────────
            "appgroep" => [
                "keywords" => ["appgroep", "signal app", "app groep werk"],
                "answer" => "We gebruiken Signal voor werk gerelateerde dingen. 📱 Geen WhatsApp!",
                "suggestions" => [
                    "Hoe meld ik me ziek via Signal?",
                    "Hoe meld ik me aan voor de Signal appgroep?",
                    "Welke app gebruiken we voor werk?"
                ],
                "sub_topics" => [
                    "welke" => [
                        "keywords" => ["welke app gebruiken we voor werk", "waarom signal niet whatsapp"],
                        "answer" => "We gebruiken Signal voor werk gerelateerde dingen 📱 Geen WhatsApp voor werkzaken!"
                    ],
                    "ziek" => [
                        "keywords" => ["signal ziek melden appgroep", "hoe meld ik me ziek via signal"],
                        "answer" => "Ziekmeldingen moeten ook telefonisch doorgegeven worden aan de dagco. App alleen is niet genoeg! 📞"
                    ],
                    "aanmelden" => [
                        "keywords" => ["aanmelden signal groep joinen link", "hoe meld ik me aan voor de signal appgroep"],
                        "answer" => "Meld je aan via de link die je van Technolab krijgt 🔗"
                    ],
                ]
            ],

            // ── E-MAILHANDTEKENING ────────────────────────────────────────────
            "emailhandtekening" => [
                "keywords" => ["emailhandtekening", "email handtekening", "handtekening outlook"],
                "answer" => "Je emailhandtekening kan je aanpassen via de Technolab Handtekening Editor. Kopieer je handtekening en plak die in Outlook. ✉️",
                "suggestions" => [
                    "Hoe maak ik een emailhandtekening aan?",
                    "Hoe open ik de handtekening editor?",
                    "Hoe plak ik mijn handtekening in Outlook?"
                ],
                "sub_topics" => [
                    "aanmaken" => [
                        "keywords" => ["emailhandtekening aanmaken hoe maken", "hoe maak ik een emailhandtekening aan"],
                        "answer" => "Ga naar de Handtekening Editor op <a href='https://technolab-intern.nl/Emailhandtekening/' target='_blank'>technolab-intern.nl/Emailhandtekening/</a>, vul je persoonsgegevens in, en klik op de paarse knop 'Kopieer voor Outlook'. Plak dit daarna in Outlook via Instellingen → Account → Handtekeningen → Handtekening toevoegen. 📖"
                    ],
                    "editor" => [
                        "keywords" => ["handtekening editor openen website", "hoe open ik de handtekening editor"],
                        "answer" => "De Handtekening Editor open je via: <a href='https://technolab-intern.nl/Emailhandtekening/' target='_blank'>technolab-intern.nl/Emailhandtekening/</a>. Vul je gegevens in, kies of je een banner wil, en kopieer je handtekening via de paarse knop. 🖥️"
                    ],
                    "banner" => [
                        "keywords" => ["banner handtekening toevoegen aanzetten"],
                        "answer" => "In de Handtekening Editor kan je een banner aanzetten door het vakje bij 'banner' aan te vinken. Er komen later mogelijk meer banner-opties — op 1 juli 2026 volgt de 'Techniek & Toekomst' banner. 🖼️"
                    ],
                    "plakken" => [
                        "keywords" => ["handtekening plakken outlook ctrl v", "hoe plak ik mijn handtekening in outlook"],
                        "answer" => "Kopieer je handtekening via de paarse knop in de editor. Ga in Outlook naar ⚙️ Instellingen → Account → Handtekeningen → Handtekening toevoegen. Geef hem een naam en plak met CTRL+V. Stel hem in als standaard voor nieuwe én doorgestuurde berichten en sla op. ✅"
                    ],
                    "svg" => [
                        "keywords" => ["svg handtekening werkt niet outlook probleem"],
                        "answer" => "SVG-bestanden worden niet meegenomen als je de handtekening via CTRL+V plakt, omdat Outlook Word als engine gebruikt. Wil je toch SVG gebruiken? Klik in het preview-vlak van de editor, selecteer alles met CTRL+A en sleep de handtekening via drag & drop naar het handtekening-veld in Outlook. 🔧"
                    ],
                    "opslaan" => [
                        "keywords" => ["handtekening opslaan html downloaden bewaren"],
                        "answer" => "Je kan je handtekening opslaan door de HTML-code te downloaden vanuit de editor. Zo kan je hem later opnieuw gebruiken zonder alles opnieuw in te vullen. 💾"
                    ],
                    "problemen" => [
                        "keywords" => ["handtekening werkt niet fout probleem mobiel"],
                        "answer" => "Op mobiele apparaten kan de handtekening er iets anders uitzien dan op desktop — dat is normaal. Gaat er iets fout? Neem contact op met Pieter via pieter@technolableiden.nl. 📬"
                    ],
                ]
            ],

            // ── HOLACRATIE / WERKOVERLEG ──────────────────────────────────────
            "holacratie" => [
                "keywords" => ["holacratie", "holacratisch", "werkoverleg cirkel"],
                "answer" => "Holacratisch werkoverleg is strak vergaderen volgens vaste regels. Elke cirkel heeft wekelijks zo'n overleg. 🔄",
                "suggestions" => [
                    "Wat is holacratie?",
                    "Wie is de facilitator bij holacratie?",
                    "Wat is een holacratie cirkel?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["holacratie wat is uitleg betekent", "wat is holacratie"],
                        "answer" => "Holacratisch werkoverleg is strak vergaderen volgens vaste regels. Elke cirkel heeft wekelijks zo'n overleg 📅"
                    ],
                    "facilitator" => [
                        "keywords" => ["holacratie facilitator secretaris leider", "wie is de facilitator bij holacratie"],
                        "answer" => "De facilitator (gekozen per periode) leidt het overleg. De secretaris zorgt dat taken in de teamsplanner worden vastgelegd ✍️"
                    ],
                    "cirkel" => [
                        "keywords" => ["holacratie cirkel wat is team", "wat is een holacratie cirkel"],
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
                        "keywords" => ["planner hoe werkt agenda cirkel", "hoe werkt de planner"],
                        "answer" => "Elke cirkel heeft een planner die de agenda beheert en veranderingen afstemmt 📋"
                    ],
                    "wachtwoord" => [
                        "keywords" => ["planner wachtwoord toegang vergrendeld", "waar vind ik het planner wachtwoord"],
                        "answer" => "De agenda is vergrendeld met wachtwoord. Vraag het bij de hoofdplanner in overleg met je rolverdeler 🔒"
                    ],
                    "teamleden" => [
                        "keywords" => ["planner teamleden overzicht werkdagen tabblad", "wie staan er in de planner als teamleden"],
                        "answer" => "In de agenda zit een tabblad met alle teamleden, stagiairs en hun werkdagen. Zorg dat jij er ook bij staat! 👥"
                    ],
                ]
            ],

            // ── TOEKOMSTKUNDE (standalone) ────────────────────────────────────
            "toekomstkunde" => [
                "keywords" => [
                    "toekomstkunde",
                    "natuur en techniek lessen",
                    "natuur techniek po vo"
                ],
                "answer" => "Toekomstkunde is ons lesaanbod natuur- & technieklessen voor PO en VO, gericht op drie thema's: Energie en milieu, Technologische innovatie, en Leven en omgeving 🌱⚙️🌍 <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>technolableiden.nl/toekomstkunde</a>",
                "suggestions" => [
                    "Wat is Energie en milieu?",
                    "Wat is Technologische innovatie?",
                    "Wat is Leven en omgeving?"
                ],
                "sub_topics" => [
                    "energie_milieu" => [
                        "keywords" => ["toekomstkunde energie en milieu duurzaamheid", "klimaatactie les toekomstkunde"],
                        "answer" => "Leerlingen onderzoeken hernieuwbare energie, biodiversiteit, circulaire economie en klimaatactie, en bedenken zelf oplossingen voor duurzaamheidsvraagstukken 🌱 <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer info — klik hier! →</a>"
                    ],
                    "technologische_innovatie" => [
                        "keywords" => ["toekomstkunde technologische innovatie stroomcircuits"],
                        "answer" => "Leerlingen verkennen technologie en wetenschap via workshops over stroomcircuits, tandwielen en katrollen, en ontwikkelen creativiteit en probleemoplossend vermogen ⚙️ <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer info — klik hier! →</a>"
                    ],
                    "leven_omgeving" => [
                        "keywords" => ["toekomstkunde leven en omgeving programmeren robots"],
                        "answer" => "Leerlingen leren programmeren via o.a. de Micro:bit en bouwen eigen robots om problemen op te lossen, en leggen zo een basis voor computatief denken 🤖 <a href='https://www.technolableiden.nl/toekomstkunde/' target='_blank'>Meer info — klik hier! →</a>"
                    ],
                    "groepen" => [
                        "keywords" => ["toekomstkunde lessen per groep po vo mbo"],
                        "answer" => "Er is lesaanbod voor groep 1/2 t/m groep 8, VO en MBO – van Robot Ontdeklab en Kleine Muis tot Hackerspace, DNA, Marsbots en Techniek en Duurzaamheid 📚"
                    ],
                    "onderzoekend_ontwerpend" => [
                        "keywords" => ["toekomstkunde onderzoekend ontwerpend leren", "wat is onderzoekend ontwerpend leren"],
                        "answer" => "Bij onderzoekend leren staat vragen stellen en experimenteren centraal, bij ontwerpend leren gaat het om bedenken, bouwen en testen van oplossingen in interactieve stappen 🔍"
                    ],
                    "locatie_lessen" => [
                        "keywords" => ["toekomstkunde lessen locatie kluslokaal school"],
                        "answer" => "Lessen sluiten zoveel mogelijk aan op het curriculum en worden deels op school gegeven. Materiaalintensieve workshops worden bij Technolab gegeven, in een volledig uitgerust kluslokaal 🏫"
                    ],
                ]
            ],

            // ── ZIJINSTROMERS ─────────────────────────────────────────────────
            "zijinstromers" => [
                "keywords" => ["zijinstromers", "zij instromers", "carriere switch onderwijs", "overstap naar onderwijs"],
                "answer" => "Voor mensen die een carrièreswitch naar het onderwijs of de techniek overwegen, biedt Technolab drie programma's: Expeditie Leerkracht, Meesterchallenge en Techniek en Toekomst 🚀 <a href='https://www.technolableiden.nl/zijinstromers/' target='_blank'>technolableiden.nl/zijinstromers</a>",
                "suggestions" => [
                    "Wat is Expeditie Leerkracht?",
                    "Wat is de Meesterchallenge?",
                    "Wat is Techniek en Toekomst?"
                ],
                "sub_topics" => [
                    "overzicht" => [
                        "keywords" => ["welke programmas voor zijinstromers overzicht opties"],
                        "answer" => "Expeditie Leerkracht is een tweedaagse kennismaking met het onderwijsvak, de Meesterchallenge is een 10 weken durend leer-werktraject in het onderwijs, en Techniek en Toekomst helpt je de stap te zetten naar de techniek 🎯 <a href='https://www.technolableiden.nl/zijinstromers/' target='_blank'>Meer info — klik hier! →</a>"
                    ],
                ]
            ],

            // ── EXPEDITIE LEERKRACHT ──────────────────────────────────────────
            "expeditie_leerkracht" => [
                "keywords" => ["expeditie leerkracht", "tweedaagse onderwijs", "kennismaken met onderwijs"],
                "answer" => "Expeditie Leerkracht is een tweedaagse waarin je op een actieve, speelse en persoonlijke manier je eerste stappen zet in het onderwijsvak. Een samenwerking tussen Hogeschool Leiden en Technolab 👩‍🏫 <a href='https://www.technolableiden.nl/zijinstromers/expeditie-leerkracht/' target='_blank'>technolableiden.nl/expeditie-leerkracht</a>",
                "suggestions" => [
                    "Wat kost Expeditie Leerkracht?",
                    "Wanneer is de volgende Expeditie Leerkracht?",
                    "Hoe ziet dag 1 en dag 2 eruit?"
                ],
                "sub_topics" => [
                    "kosten" => [
                        "keywords" => ["expeditie leerkracht kosten prijs bedrag", "wat kost expeditie leerkracht"],
                        "answer" => "De kosten zijn voor schooljaar 2025-2026 verlaagd van €500,- naar €250,-, dankzij bijdrage van de onderwijsregio's Leiden, Duin- en Bollenstreek en Haaglanden 💶 <a href='https://www.technolableiden.nl/zijinstromers/expeditie-leerkracht/' target='_blank'>Meer info & aanmelden — klik hier! →</a>"
                    ],
                    "data" => [
                        "keywords" => ["expeditie leerkracht data wanneer datum", "wanneer is de volgende expeditie leerkracht"],
                        "answer" => "Komende edities: 15-16 juni (VOL), 5-6 oktober 2026 (Den Haag, Inholland), 8-9 maart 2027 (Den Haag, HHS), 7-8 juni 2027 (Den Haag, Inholland) 📅 <a href='https://www.technolableiden.nl/zijinstromers/expeditie-leerkracht/' target='_blank'>Aanmelden — klik hier! →</a>"
                    ],
                    "programma" => [
                        "keywords" => ["expeditie leerkracht dag 1 dag 2 programma", "hoe ziet dag 1 en dag 2 eruit"],
                        "answer" => "Dag 1 (8:30-17:00): 'De drempel over' — kennismaken, klas op Technolab bekijken en zelf een mini-les ontwerpen en testen. Dag 2 (8:00-16:00): 'De beproeving' — zelf een les geven op een school 📖"
                    ],
                    "locatie" => [
                        "keywords" => ["expeditie leerkracht locatie waar den haag"],
                        "answer" => "De edities vinden plaats in Den Haag, bij Inholland of HHS 📍"
                    ],
                ]
            ],

            // ── MEESTERCHALLENGE ──────────────────────────────────────────────
            "meesterchallenge" => [
                "keywords" => ["meesterchallenge", "10 weken challenge onderwijs", "leer werktraject onderwijs"],
                "answer" => "De Meesterchallenge is een 10 weken durende challenge waarbij je 3 dagen per week, samen met je team, workshops ontwikkelt en geeft binnen natuur, techniek en technologie. Ideaal als tussenjaar of carrièreswitch! 🎓 <a href='https://www.technolableiden.nl/zijinstromers/meesterchallenge-2/' target='_blank'>technolableiden.nl/meesterchallenge</a>",
                "suggestions" => [
                    "Hoeveel vergoeding krijg ik bij de Meesterchallenge?",
                    "Wanneer kan ik starten met de Meesterchallenge?",
                    "Voor wie is de Meesterchallenge?"
                ],
                "sub_topics" => [
                    "vergoeding" => [
                        "keywords" => ["meesterchallenge vergoeding geld betaling", "hoeveel vergoeding krijg ik bij de meesterchallenge"],
                        "answer" => "Voor de Meesterchallenge ontvang je een vergoeding van €700 💶 <a href='https://www.technolableiden.nl/zijinstromers/meesterchallenge-2/' target='_blank'>Meer info — klik hier! →</a>"
                    ],
                    "data" => [
                        "keywords" => ["meesterchallenge data wanneer starten periodes", "wanneer kan ik starten met de meesterchallenge"],
                        "answer" => "Komende periodes: 14 sept t/m 27 nov 2026, 26 okt t/m 15 jan 2027, 4 jan t/m 19 mrt 2027, 15 mrt t/m 4 juni 2027, 10 mei t/m 16 juli 2027 (Technolab is dicht in schoolvakanties) 📅 <a href='https://www.technolableiden.nl/zijinstromers/meesterchallenge-2/' target='_blank'>Aanmelden — klik hier! →</a>"
                    ],
                    "doelgroep" => [
                        "keywords" => ["meesterchallenge voor wie doelgroep eisen diploma", "voor wie is de meesterchallenge"],
                        "answer" => "Voor iedereen die zijn/haar talenten wil ontdekken: een tussenjaar, carrièreswitch of zij-instroomtraject. Geen diploma of bèta-achtergrond nodig, wel een aanpakker die houdt van doen 🙌 <a href='https://www.technolableiden.nl/zijinstromers/meesterchallenge-2/' target='_blank'>Meer info — klik hier! →</a>"
                    ],
                    "inhoud" => [
                        "keywords" => ["meesterchallenge programma trainingen inhoud", "wat leer je bij de meesterchallenge"],
                        "answer" => "Je ontwikkelt en geeft workshops in teamverband en krijgt trainingen 'pedagogiek en didactiek' en 'persoonlijke ontwikkeling', waarbij je leert wat actief leren is en hoe je orde houdt 📚"
                    ],
                    "sollicitatie" => [
                        "keywords" => ["meesterchallenge solliciteren aanmelden procedure"],
                        "answer" => "De procedure bestaat uit een kennismakingscall met een coach en een meeloopdag, waarna jullie samen ontdekken of er een match is ✅ <a href='https://www.technolableiden.nl/zijinstromers/meesterchallenge-2/' target='_blank'>Aanmelden — klik hier! →</a>"
                    ],
                ]
            ],

            // ── TECHNIEK EN TOEKOMST ──────────────────────────────────────────
            "techniek_en_toekomst" => [
                "keywords" => ["techniek en toekomst", "techniek toekomst leerwerktraject"],
                "answer" => "Techniek & Toekomst verbindt bedrijven met technisch talent. Het bestaat uit drie stappen: een Expeditie (2 dagen), een Challenge (2 weken) en een Stage (10 weken) 🔧 <a href='https://www.technolableiden.nl/zijinstromer-techniek-en-toekomst/' target='_blank'>technolableiden.nl/techniek-en-toekomst</a>",
                "suggestions" => [
                    "Wat is de Expeditie Techniek en Toekomst?",
                    "Wat is de Challenge?",
                    "Wat is de Stage bij Techniek en Toekomst?"
                ],
                "sub_topics" => [
                    "expeditie" => [
                        "keywords" => ["expeditie techniek en toekomst 2 dagen oriëntatie", "wat is de expeditie techniek en toekomst"],
                        "answer" => "De Expeditie duurt 2 dagen: je zet op een actieve manier je eerste stappen in de wereld van techniek en ontdekt welke sector bij je past. Komende editie: 29-30 juni 2026 📅 <a href='https://www.technolableiden.nl/zijinstromer-techniek-en-toekomst/' target='_blank'>Aanmelden — klik hier! →</a>"
                    ],
                    "challenge" => [
                        "keywords" => ["techniek en toekomst challenge 2 weken opdracht bedrijf", "wat is de challenge techniek toekomst"],
                        "answer" => "De Challenge duurt 2 weken: je werkt aan een echte techniekopdracht bij een bedrijf en verdiept je in een specifieke sector 🔨 <a href='https://www.technolableiden.nl/zijinstromer-techniek-en-toekomst/' target='_blank'>Meer info — klik hier! →</a>"
                    ],
                    "stage" => [
                        "keywords" => ["techniek en toekomst stage 10 weken bedrijf sector", "wat is de stage bij techniek en toekomst"],
                        "answer" => "De Stage duurt 10 weken: je loopt drie dagen per week mee bij een bedrijf in jouw gekozen sector, gericht op een opleiding of baan in de techniek 💼 <a href='https://www.technolableiden.nl/zijinstromer-techniek-en-toekomst/' target='_blank'>Meer info — klik hier! →</a>"
                    ],
                    "aanmelden" => [
                        "keywords" => ["aanmelden techniek en toekomst formulier website"],
                        "answer" => "Je kunt je aanmelden voor de Expeditie Techniek en Toekomst via het aanmeldformulier op de website, of contact opnemen voor meer informatie 📝 <a href='https://www.technolableiden.nl/zijinstromer-techniek-en-toekomst/' target='_blank'>Aanmelden — klik hier! →</a>"
                    ],
                ]
            ],

            // ── BEDRIJVEN ─────────────────────────────────────────────────────
            "bedrijven" => [
                "keywords" => ["bedrijven partner technolab", "samenwerking bedrijven technolab"],
                "answer" => "Technolab werkt samen met bedrijven via Techniek en Toekomst (technisch talent vinden), Talent & Toekomst (loopbaanoriëntatie voor scholieren) en workshops voor volwassen teams 🤝 <a href='https://www.technolableiden.nl/bedrijven/' target='_blank'>technolableiden.nl/bedrijven</a>",
                "suggestions" => [
                    "Wat is Techniek en Toekomst voor bedrijven?",
                    "Wat is Talent & Toekomst?",
                    "Bieden jullie ook workshops voor teams?"
                ],
                "sub_topics" => [
                    "techniek_toekomst_bedrijven" => [
                        "keywords" => ["wat is techniek en toekomst voor bedrijven", "bedrijven techniek talent vinden"],
                        "answer" => "Via Techniek en Toekomst vinden technisch talent en bedrijven elkaar: van oriënteren tot opleiden, wij maken de stap van dromen naar doen 🔧 <a href='https://www.technolableiden.nl/bedrijven/' target='_blank'>Meer info voor bedrijven — klik hier! →</a>"
                    ],
                    "talent_toekomst_bedrijven" => [
                        "keywords" => ["wat is talent toekomst voor bedrijven", "talent toekomst stage mavo bedrijven"],
                        "answer" => "Bij Talent & Toekomst lopen mavoleerlingen van het Bonaventura College stage bij bedrijven zoals PLNT, Kleine Planeet en Easyfiets, in sectoren als ICT, Ondernemen, Onderwijs en Zorg 🏢 <a href='https://www.technolableiden.nl/bedrijven/' target='_blank'>Meer info — klik hier! →</a>"
                    ],
                    "workshops_volwassenen" => [
                        "keywords" => ["bieden jullie ook workshops voor teams", "workshops teams bedrijven technolab duurzaamheid"],
                        "answer" => "Ben je op zoek naar een unieke manier om je team aan te zetten voor duurzaamheid, robotica of de digitale wereld? Meld je team aan voor een workshopdag op Technolab. Binnenkort meer informatie 🛠️ <a href='https://www.technolableiden.nl/bedrijven/' target='_blank'>Meer info voor bedrijven — klik hier! →</a>"
                    ],
                    "partners" => [
                        "keywords" => ["partners technolab wie zijn de partners bedrijven"],
                        "answer" => "Onder andere Ondernemersfonds Leiden, Plus, DZB, MBO Rijnland, UWV, Hortus Botanicus, Leiden Bio Science Park, CHDR, Gemeente Leiden, Zooma, Holland Rijnland en meer 🤝 <a href='https://www.technolableiden.nl/bedrijven/' target='_blank'>technolableiden.nl/bedrijven</a>"
                    ],
                ]
            ],

            // ── TALENT EN TOEKOMST ────────────────────────────────────────────
            "talent_en_toekomst" => [
                "keywords" => ["talent en toekomst", "vijfdaagse stage leerlingen", "stage vo leerlingen"],
                "answer" => "Talent & Toekomst is een vijfdaagse activerende stage voor VO-leerlingen om alle ins en outs van vier vakgebieden te ontdekken: Ondernemen, Onderwijs, Techniek en Zorg 🧭 <a href='https://www.technolableiden.nl/scholen/voortgezet-onderwijs/talent-en-toekomst/' target='_blank'>technolableiden.nl/talent-en-toekomst</a>",
                "suggestions" => [
                    "Voor welke leerlingen is Talent & Toekomst?",
                    "Bij welke bedrijven loop je stage?",
                    "Hoe werkt de stageweek?"
                ],
                "sub_topics" => [
                    "doelgroep" => [
                        "keywords" => ["voor welke leerlingen is talent toekomst", "talent toekomst doelgroep school mavo"],
                        "answer" => "Talent & Toekomst is bedoeld voor middelbare scholieren, momenteel mavoleerlingen van het Bonaventura College 🎓 <a href='https://www.technolableiden.nl/scholen/voortgezet-onderwijs/talent-en-toekomst/' target='_blank'>Meer info — klik hier! →</a>"
                    ],
                    "sectoren" => [
                        "keywords" => ["talent toekomst sectoren vakgebieden ict zorg"],
                        "answer" => "Leerlingen verkennen vier sectoren: ICT, Ondernemen, Onderwijs en Zorg 🔍"
                    ],
                    "bedrijven_stage" => [
                        "keywords" => ["bij welke bedrijven loop je stage talent toekomst", "talent toekomst stagebedrijven plnt"],
                        "answer" => "Leerlingen lopen stage bij bedrijven zoals PLNT, Kleine Planeet en Easyfiets 🏢 <a href='https://www.technolableiden.nl/scholen/voortgezet-onderwijs/talent-en-toekomst/' target='_blank'>Meer info — klik hier! →</a>"
                    ],
                    "doel" => [
                        "keywords" => ["hoe werkt de stageweek talent toekomst", "doel talent toekomst studiekeuze"],
                        "answer" => "Het programma helpt leerlingen groeien in beroepsbeelden, beroepsgerichte kennis en vaardigheden, zodat ze een betere studiekeuze kunnen maken 🎯 <a href='https://www.technolableiden.nl/scholen/voortgezet-onderwijs/talent-en-toekomst/' target='_blank'>Meer info — klik hier! →</a>"
                    ],
                ]
            ],

            // ── BUDDY / COACHING ──────────────────────────────────────────────
            "coaching" => [
                "keywords" => ["buddy coaching", "coach technolab"],
                "answer" => "Elke medewerker zoekt een buddy om leerdoelen te bespreken. Coaches helpen met persoonlijke uitdagingen. 🤝",
                "suggestions" => [
                    "Wat is het buddy systeem?",
                    "Hoe werkt het coaching traject?",
                    "Wie is mijn coach?"
                ],
                "sub_topics" => [
                    "buddy" => [
                        "keywords" => ["buddy systeem leerdoel technolab evalueren", "wat is het buddy systeem"],
                        "answer" => "Elke medewerker zoekt een buddy binnen Technolab om eigen leerdoelen te bespreken en te evalueren 🎯"
                    ],
                    "coach" => [
                        "keywords" => ["coaching traject afspraken persoonlijk", "hoe werkt het coaching traject"],
                        "answer" => "Coaches helpen in een traject van 3-4 afspraken met persoonlijke uitdagingen 💬"
                    ],
                    "wie" => [
                        "keywords" => ["coach wie is mijn coach organigram", "wie is mijn coach technolab"],
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
                        "keywords" => ["wie is de vertrouwenspersoon naam", "vertrouwenspersoon maartje"],
                        "answer" => "Maartje Kapteijn is onze vertrouwenspersoon."
                    ]
                ]
            ],

            // ── BUS RIJDEN ────────────────────────────────────────────────────
            "bus" => [
                "keywords" => ["bus", "vervoer", "bus rijden", "bus reserveren technolab", "vervoer technolab", "fiets reserveren", "busje"],
                "answer" => "Heb je een rijbewijs? Dan moet je eerst een proefrit doen. Daarna mag je ermee rijden! 🚐",
                "suggestions" => [
                    "Hoe reserveer ik de bus?",
                    "Wat zijn de regels voor het rijden met de bus?"
                ],
                "sub_topics" => [
                    "rijden" => [
                        "keywords" => ["bus rijden proefrit rijbewijs technolab", "wat zijn de regels voor het rijden met de bus"],
                        "answer" => "Heb je een rijbewijs? Dan moet je eerst een proefrit met de Technolab bus doen. Pas daarna mag je ermee rijden 🚗"
                    ],
                    "reserveren" => [
                        "keywords" => ["bus reserveren dagco wiki fiets", "hoe reserveer ik de bus"],
                        "answer" => "Reserveer via de Dagco Wiki! Dit geldt ook voor fietsen! 📅"
                    ],
                ]
            ],

            // ── BOEKHOUDING / INKOPEN ─────────────────────────────────────────
            "boekhouding" => [
                "keywords" => ["boekhouding", "inkopen technolab", "bonnetje declareren", "pinpas technolab"],
                "answer" => "Bij Gamma of Plus koop je met je Technolab pasje. Bonnetje inleveren in de kast in de Groei! 🧾",
                "suggestions" => [
                    "Hoe betaal ik zelf iets voor boekhouding?",
                    "Hoe gebruik ik de pinpas voor boekhouding?",
                    "Hoe bestel ik iets online via boekhouding?"
                ],
                "sub_topics" => [
                    "gamma_plus" => [
                        "keywords" => ["boekhouding gamma plus pasje inkopen"],
                        "answer" => "Bij de Gamma of Plus koop je met je Technolab pasje 🪪 Neem bij de Gamma ook de Gamma-pas mee! Bonnetje in kast in de Groei!"
                    ],
                    "voorschieten" => [
                        "keywords" => ["boekhouding zelf betalen voorschieten terugkrijgen", "hoe betaal ik zelf iets voor boekhouding"],
                        "answer" => "Stuur foto van bonnetje + rekeninggegevens naar boekhouding@technolableiden.nl ✉️ Vraag altijd akkoord van producteigenaar!"
                    ],
                    "pinpas" => [
                        "keywords" => ["boekhouding pinpas gebruiken code bonnetje", "hoe gebruik ik de pinpas voor boekhouding"],
                        "answer" => "Foto van bonnetje naar boekhouding en origineel in kast in Groei 🧾 Vraag waar pinpas en code zijn!"
                    ],
                    "online" => [
                        "keywords" => ["boekhouding online bestellen link", "hoe bestel ik iets online via boekhouding"],
                        "answer" => "Stuur op tijd een link naar boekhouding — liefst met akkoord van producteigenaar 🛒"
                    ],
                    "overig" => [
                        "keywords" => ["boekhouding overige kosten geen bonnetje parkeren"],
                        "answer" => "Andere uitgaven zonder bonnetje? Bespreek met boekhouding, we vinden samen een oplossing 🤝"
                    ],
                    "voorraad" => [
                        "keywords" => ["boekhouding voorraad op toiletpapier koffie thee"],
                        "answer" => "Voorraad op raakt? Laat het boekhouding/inkoop weten! 📦"
                    ],
                ]
            ],

            // ── PAPIER HERGEBRUIKEN ───────────────────────────────────────────
            "papier" => [
                "keywords" => ["papier hergebruiken", "papier recyclen", "papier brein", "papier dubbelzijdig"],
                "answer" => "Op Technolab hergebruiken we papier! ♻️ Sorteer papier in het brein: dubbelzijdig of engelszijdig bedrukt.",
            ],
        ];
        $this->buildSuggestionMap();
    }

    private function initializeSynonyms(): void
    {
        $this->synonyms = [
            // Loon / salaris
            "loon"              => ["salaris", "betaling", "uitbetaling", "verdienste", "inkomsten", "geld", "loonstrook", "vergoeding", "inkomen", "gage", "arbeidsvergoeding"],
            "salaris"           => ["loon", "betaling", "uitbetaling", "verdienste", "inkomen", "vergoeding", "gage"],
            "betaling"          => ["uitbetaling", "loon", "salaris", "geld", "giro", "overschrijving", "storting", "betalingen"],

            // Lessen / onderwijs
            "lessen"            => ["les", "workshop", "lesaanbod", "lesprogramma", "training", "cursus", "les geven", "onderwijs", "educatie", "instructie", "activiteit"],
            "workshop"          => ["les", "training", "cursus", "sessie", "bijeenkomst", "werksessie", "activiteit", "programma", "les geven"],
            "techniekwijs"      => ["techniek", "wetenschap", "electronics", "maker", "stroomcircuit", "tandwiel", "katrol", "uitvinder"],
            "programmeren"      => ["coderen", "coding", "digitaal", "toekomsttaal", "computeren", "software", "scripting", "algoritme", "microbit"],
            "duurzaamheid"      => ["groen", "klimaat", "toekomstkunde", "milieu", "circulair", "energie", "hernieuwbaar", "biodiversiteit", "ecologie"],

            // Dagco / organisatie
            "dagco"             => ["dagcoordinator", "dagcoördinator", "coordinator", "dagplanner", "openingsdienst", "dienstdoende"],
            "holacratie"        => ["werkoverleg", "cirkel", "team overleg", "cirkeloverleg", "zelfsturend", "scrum", "agile", "autonomie", "zelforganisatie"],
            "planner"           => ["agenda", "teamplanner", "rooster", "planning", "schema", "kalender", "werkschema", "dienstrooster"],

            // Stage / werk
            "stage"             => ["stagiair", "stageplaats", "stagelopen", "praktijk", "leertraject", "beroepspraktijk", "werkplek", "stageplek", "leerwerkplek"],
            "medewerker"        => ["werknemer", "collega", "teamlid", "personeel", "stagiair", "medewerkers"],

            // Pasje / toegang
            "pasje"             => ["badge", "liftpas", "toegangspas", "kaart", "keycard", "pas", "toegangskaart", "identiteitskaart"],

            // BHV / veiligheid
            "bhv"               => ["bedrijfshulpverlening", "noodgeval", "ehbo", "veiligheid", "brandwacht", "calamiteit", "hulpverlener", "ontruiming"],
            "noodgeval"         => ["calamiteit", "incident", "brand", "ongeluk", "gevaar", "emergency", "alarm", "ontruiming"],

            // Fika / koken
            "fika"              => ["lunch", "samen eten", "gezamenlijke maaltijd", "woensdag lunch", "koken", "maaltijd", "groepslunch", "community lunch"],

            // Bus / vervoer
            "bus"               => ["vervoer", "auto", "voertuig", "busje", "transport", "minibus", "wagen", "rijden", "mobiliteit"],
            "vervoer"           => ["bus", "fiets", "auto", "transport", "mobiliteit", "rijden", "reizen"],

            // Boekhouding / geld
            "boekhouding"       => ["administratie", "financiën", "declaratie", "inkoop", "bonnetje", "rekening", "uitgaven", "factuur", "begroting"],
            "declareren"        => ["terugkrijgen", "vergoed krijgen", "voorschieten", "indienen", "declaratie", "terugvorderen"],

            // Pensioen
            "pensioen"          => ["brightpensioen", "pensioenfonds", "ouderdomspensioen", "lijfrente", "pensioenregeling", "pensioenbijdrage", "pensioenopbouw"],

            // MDT
            "mdt"               => ["maatschappelijke diensttijd", "vrijwilligerswerk", "maatschappelijk", "gemeenschap", "subsidie", "jongerenwerk"],

            // VOG
            "vog"               => ["verklaring omtrent gedrag", "integriteitsverklaring", "screenen", "screening", "achtergrondcheck", "bewijs goed gedrag"],

            // Huisregels / gedrag
            "huisregels"        => ["regels", "gedragscode", "protocol", "richtlijnen", "afspraken", "normen", "huisorde", "werkafspraken"],

            // Email handtekening
            "emailhandtekening" => ["handtekening", "outlook handtekening", "mail handtekening", "signature", "email signature", "visitekaartje email"],

            // Urenregistratie
            "urenregistratie"   => ["uren", "tijdregistratie", "werkuren", "tijdschrijven", "klokken", "uren bijhouden", "uren noteren"],

            // Zijinstromers
            "zijinstromers"     => ["zij-instromers", "carriere switch", "overstap", "herintreder", "omscholing", "beroepsverandering", "nieuw vak", "van baan wisselen"],
            "meesterchallenge"  => ["10 weken", "challenge onderwijs", "leer werktraject", "traject", "programma onderwijs"],

            // Techniek en Toekomst
            "techniek"          => ["technisch", "ambacht", "vakman", "installatie", "constructie", "bouw", "machinerie", "engineering"],

            // Bedrijven / samenwerking
            "bedrijven"         => ["bedrijf", "organisatie", "werkgever", "partners", "samenwerking", "ondernemers", "sponsoren", "opdrachtgevers"],

            // Leskisten
            "leskisten"         => ["kisten", "blauwe kisten", "materiaalkoffer", "lesmateriaal", "leskist", "materiaalbox", "lesbox"],

            // Coaching / buddy
            "coaching"          => ["begeleiding", "mentoring", "ondersteuning", "persoonlijke ontwikkeling", "coach", "buddy", "leerbegeleiding"],

            // Who / how / when — question helpers
            "wie"               => ["welke persoon", "naam", "contact", "medewerker", "collega"],
            "wat"               => ["welke", "soort", "type", "betekenis", "uitleg"],
            "hoe"               => ["op welke manier", "werkwijze", "proces", "stappen", "procedure"],
            "waar"              => ["locatie", "plek", "plaats", "adres", "gebouw"],
            "wanneer"           => ["tijdstip", "moment", "dag", "uur", "datum", "periode"],
        ];
    }

    private function initializeSemanticGroups(): void
    {
        $this->semanticGroups = [
            // Financieel
            "financial"         => ["loon", "salaris", "betaling", "geld", "verdienen", "uitbetaling", "pensioen", "brightpensioen", "declareren", "boekhouding", "vergoeding", "inkomen", "factuur"],

            // Onderwijs / lessen
            "education"         => ["lessen", "les", "workshop", "techniekwijs", "toekomsttaal", "toekomstkunde", "programmeren", "educatie", "lesaanbod", "lesprogramma", "leskisten", "popup"],

            // Organisatie / structuur
            "organization"      => ["dagco", "holacratie", "cirkel", "team", "planner", "werkoverleg", "rolverdeler", "facilitator", "secretaris", "agenda", "teamplanner"],

            // Werk / stage / loopbaan
            "work"              => ["stage", "werk", "job", "stagiair", "medewerker", "contract", "arbeidscontract", "leerwerkplek", "beroepspraktijk", "leertraject"],

            // Regels / beleid
            "rules"             => ["huisregels", "gedrag", "regels", "protocol", "richtlijn", "gedragscode", "afspraken", "normen"],

            // Veiligheid / noodgevallen
            "safety"            => ["bhv", "bedrijfshulpverlening", "noodgeval", "ehbo", "brandwacht", "calamiteit", "hulpverlener", "alarm", "ontruiming", "veiligheid"],

            // Projectdagen / basisschool
            "projects"          => ["projectdag", "botsende bots", "groene daken", "mens en robot", "duurzaam huis", "avontuurlijke architecten", "ontwerp je attractie", "basisschool", "leerlingen"],

            // Vervoer / mobiliteit
            "transport"         => ["bus", "fiets", "vervoer", "auto", "rijden", "mobiliteit", "transport", "busje", "wagen", "reserveren"],

            // Communicatie / digitaal
            "communication"     => ["signal", "appgroep", "whatsapp", "emailhandtekening", "email", "handtekening", "outlook", "app", "communicatie", "berichten"],

            // Toegang / gebouw
            "access"            => ["pasje", "badge", "liftpas", "sleutel", "alarm", "toegang", "gebouw", "pand", "keycard", "inchecken"],

            // Carrière switch / zijinstromers
            "career_switch"     => ["zijinstromers", "meesterchallenge", "expeditie leerkracht", "techniek en toekomst", "carriere switch", "omscholing", "herintreder", "beroepsverandering"],

            // Bedrijven / partners
            "business"          => ["bedrijven", "partner", "samenwerking", "opdrachtgever", "sponsors", "ondernemers", "organisaties", "bedrijf"],

            // Persoonlijke ontwikkeling
            "personal_growth"   => ["coaching", "buddy", "begeleiding", "mentoring", "leerdoel", "talentontwikkeling", "groeien", "vertrouwenspersoon"],

            // Talent & toekomst / scholen
            "schools"           => ["talent en toekomst", "scholen", "leerlingen", "vo", "mavo", "basisschool", "po", "mbo", "hbo", "studenten"],

            // Tijdregistratie / uren
            "time"              => ["urenregistratie", "uren", "tijdregistratie", "werkuren", "mdt", "maatschappelijke diensttijd", "rooster", "werkschema"],
        ];
    }

    private function buildSuggestionMap(): void
    {
        foreach ($this->intents as $intentName => $intent) {
            if (!isset($intent["suggestions"]) || !isset($intent["sub_topics"])) {
                continue;
            }

            foreach ($intent["suggestions"] as $suggestion) {
                $normalizedSuggestion = $this->normalizeMessage($suggestion);

                // For lessen and projectdag: try direct sub-key name match first
                // (suggestion "Robot Ontdeklab" → sub-key "robot_ontdeklab")
                if (in_array($intentName, ["lessen", "projectdag"])) {
                    $guessedKey = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $normalizedSuggestion));
                    $guessedKey = trim($guessedKey, '_');
                    if (isset($intent["sub_topics"][$guessedKey])) {
                        $this->suggestionMap[$normalizedSuggestion] = [$intentName, $guessedKey];
                        continue;
                    }
                    // Also try matching against first keyword of each sub-topic
                    foreach ($intent["sub_topics"] as $subKey => $subTopic) {
                        $firstKeyword = $subTopic["keywords"][0] ?? "";
                        if (str_contains($firstKeyword, $normalizedSuggestion) || $normalizedSuggestion === $this->normalizeMessage($firstKeyword)) {
                            $this->suggestionMap[$normalizedSuggestion] = [$intentName, $subKey];
                            continue 2;
                        }
                    }
                }

                $bestMatch = $this->findSubTopicForSuggestion($intentName, $suggestion);
                if ($bestMatch) {
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
        if (isset($this->suggestionMap[$normalizedMessage])) {
            return $this->suggestionMap[$normalizedMessage];
        }

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
        $message = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $message);
        $message = preg_replace('/\s+/', ' ', $message);
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

        $suggestionMatch = $this->findSuggestionMatch($normalizedMessage);
        if ($suggestionMatch) {
            [$intentName, $subTopicKey] = $suggestionMatch;
            return $this->getResponseForSuggestion($intentName, $subTopicKey, $normalizedMessage);
        }

        $allResults = $this->scoreAllIntents($normalizedMessage, $messageWords);

        if (empty($allResults) || $allResults[0]["score"] < 0.4) {
            return [
                "reply" => $this->defaultResponse(),
                "buttons" => []
            ];
        }

        $topScore = $allResults[0]["score"];
        $tied = array_filter($allResults, fn($r) => ($topScore - $r["score"]) <= self::TIE_THRESHOLD);
        $tied = array_values($tied);

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

        $winner = $allResults[0];
        $winnerIntent = $winner["intent"];

        $bestSubTopic = null;
        foreach ($allResults as $result) {
            if ($result["intent"] === $winnerIntent && $result["subTopic"] !== null) {
                $bestSubTopic = $result;
                break;
            }
        }

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

    private function scoreAllIntents(string $normalizedMessage, array $messageWords): array
    {
        $results = [];

        foreach ($this->intents as $intentName => $intent) {
            $intentScore = $this->calculateIntentSemanticScore($normalizedMessage, $messageWords, $intent);
            $results[] = [
                "intent"   => $intentName,
                "subTopic" => null,
                "score"    => $intentScore,
            ];

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

        usort($results, fn($a, $b) => $b["score"] <=> $a["score"]);

        return $results;
    }

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

        $multiMatchBonus = min(($matchCount - 1) * 0.05, 0.15);

        return min($maxScore + $multiMatchBonus, 1.0);
    }

    private function calculateWordToKeywordSemanticScore(string $normalizedMessage, array $messageWords, string $keyword): float
    {
        if (str_contains($normalizedMessage, $keyword)) {
            return 1.0;
        }

        $bestScore = 0.0;

        foreach ($messageWords as $word) {
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

    private function buildTieResponse(array $tiedIntents): array
    {
        $buttons = [];

        foreach ($tiedIntents as $intentName => $result) {
            $intent = $this->intents[$intentName];
            $label = $intent["suggestions"][0] ?? ucfirst($intentName);
            $buttons[] = [
                "label" => $label,
                "value" => $label,
            ];
        }

        return [
            "reply"   => "Ik weet het niet zeker 🤔 Bedoel je één van deze onderwerpen?",
            "buttons" => array_slice($buttons, 0, 4),
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

        // For lessen and projectdag: always show the full list again (no cap, no exclusion)
        if (in_array($intentName, ["lessen", "projectdag"])) {
            foreach ($intent["suggestions"] as $suggestion) {
                $response["buttons"][] = [
                    "label" => $suggestion,
                    "value" => $suggestion
                ];
            }
            return $response;
        }

        // All other intents: show up to 3 other suggestions from sub-topic or parent
        $suggestionSource = $subTopic["suggestions"] ?? $intent["suggestions"] ?? [];
        $suggestionCount = 0;
        foreach ($suggestionSource as $suggestion) {
            $normalizedSuggestion = $this->normalizeMessage($suggestion);
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

        return $response;
    }

    private function getResponse(
        string $intentName,
        array $intent,
        string $message,
        ?string $subTopic = null
    ): array {

        if ($intentName === "hallo") {
            $greeting = $this->extractGreeting($message, $intent["keywords"]);
            $answer = ucfirst($greeting) . "! Fijn je te ontmoeten! Waar kan ik je mee helpen?";

            $buttons = [];
            foreach (array_slice($intent["suggestions"] ?? [], 0, 3) as $suggestion) {
                $buttons[] = ["label" => $suggestion, "value" => $suggestion];
            }
            return ["reply" => $answer, "buttons" => $buttons];
        }

        if ($subTopic && isset($intent["sub_topics"][$subTopic])) {
            $subTopicData = $intent["sub_topics"][$subTopic];
            $response = ["reply" => $subTopicData["answer"], "buttons" => []];
            if (isset($subTopicData["image"])) {
                $response["image"] = $subTopicData["image"];
            }
            // For lessen and projectdag: show all suggestions again after a lesson answer
            if (in_array($intentName, ["lessen", "projectdag"])) {
                foreach ($intent["suggestions"] as $suggestion) {
                    $response["buttons"][] = ["label" => $suggestion, "value" => $suggestion];
                }
            }
            return $response;
        }

        // For lessen and projectdag: show ALL suggestions as buttons (no 3-cap)
        $buttons = [];
        if (in_array($intentName, ["lessen", "projectdag"])) {
            foreach ($intent["suggestions"] ?? [] as $suggestion) {
                $buttons[] = ["label" => $suggestion, "value" => $suggestion];
            }
        } else {
            foreach (array_slice($intent["suggestions"] ?? [], 0, 3) as $suggestion) {
                $buttons[] = ["label" => $suggestion, "value" => $suggestion];
            }
        }

        $response = ["reply" => $intent["answer"], "buttons" => $buttons];

        if (isset($intent["image"])) {
            $response["image"] = $intent["image"];
        }

        return $response;
    }

    private function extractGreeting(string $message, array $keywords): string
    {
        $normalizedMessage = $this->normalizeMessage($message);
        $messageWords = $this->tokenizeMessage($normalizedMessage);

        foreach ($keywords as $keyword) {
            if (str_contains($normalizedMessage, $keyword)) {
                return $keyword;
            }

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
