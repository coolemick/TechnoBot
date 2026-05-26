# 🚀 Quick Start: Testing NLP Features

## What You Just Got

✅ **Compromise.js** - Real NLP library loaded from CDN
✅ **Semantic Mappings** - 6+ topic mappings for smart understanding
✅ **Entity Extraction** - Extracts amounts, dates, times, entities
✅ **Context Awareness** - Remembers conversation context
✅ **Confidence Scoring** - Knows how confident it is in matches

---

## 🧪 Test Messages to Try

### Test 1: Salary Question (Semantic Mapping)
```
User: "How much do they pay?"
Expected: Bot understands → "loon" topic
Response: "De betaling van je loon gaat via een boekhoudingsbureau 🏢"
```

### Test 2: Cooking Question
```
User: "When do we cook together?"
Expected: Bot extracts → "woensdag" + "fika" topic
Response: "Fika is elke woensdag 📅"
```

### Test 3: Money Amount Question
```
User: "What's the budget for food?"
Expected: NLP extracts → Question type + fika topic
Response: Budget answer for Fika
```

### Test 4: Emergency Help
```
User: "What if there's an accident?"
Expected: Semantic map → "bhv" + safety context
Response: "BHV'ers zijn aanwezig om te helpen bij noodgevallen 🚨"
```

### Test 5: Access Card Question
```
User: "How do I open the door?"
Expected: Semantic map → "pasje" topic
Response: "Je kunt een pasje aanvragen bij de coördinator medewerkers 🙋"
```

### Test 6: Time & Amount Combined
```
User: "How many hours should I work between 8 and 5?"
Expected: 
  - Extracts: "8:00", "17:00" (times)
  - Recognizes: Question about working hours
  - Maps to: "huisregels" or "urenregistratie"
Response: Working hours information
```

---

## 🔍 Check NLP Analysis in Browser

### Open Developer Console (F12)

**Option 1: Look at Network Requests**
1. Open DevTools → Network tab
2. Send a message to bot
3. Click on the `chat.php` POST request
4. Look at **Request** → Preview to see what NLP data was sent

**Option 2: Console Logging**
Edit Script.js and add logging:
```javascript
function enhanceMessageWithNLP(message) {
    const entities = extractEntities(message);
    const semanticMatch = findSemanticMatch(entities);
    
    console.log("🧠 NLP Analysis:", {
        message: message,
        entities: entities,
        semantic_match: semanticMatch
    });
    
    return { ... };
}
```

Then check console output when you send messages.

---

## 📊 Expected Output Format

When you send a message, the NLP analysis looks like:

```javascript
{
  message: "How much salary do I get?",
  entities: {
    original_message: "How much salary do I get?",
    nouns: ["salary"],
    verbs: ["get"],
    numbers: [],
    dates: [],
    people: [],
    is_question: true,
    has_positive: false,
    has_negative: false,
    tokens: ["much", "salary"]
  },
  semantic_match: {
    matched_term: "salary",
    intent: "loon",
    confidence: 0.8
  }
}
```

---

## 🎯 Features to Verify

- [ ] **Semantic Mapping Works**: Try salary question → gets "loon" response
- [ ] **Entity Extraction Works**: Send message with "€50" → extracted
- [ ] **Date Extraction Works**: Send "woensdag" → extracted as date
- [ ] **Question Type Detection**: Send "Hoeveel?" → detected as amount_question
- [ ] **Context Awareness**: Ask about fika, then follow-up → bot remembers
- [ ] **Fallback Works**: Send gibberish → still gets default response

---

## 📝 Sample Test Cases

| Message | Expected Intent | Expected Entity |
|---------|-----------------|-----------------|
| "What's the salary?" | loon | question_type: definition |
| "Hoeveel mag ik verdienen?" | loon | question_type: amount |
| "Wanneer eten we?" | fika | extracted_dates: woensdag |
| "Budget rond 50 euro?" | fika | extracted_amounts: 50 |
| "Emergency protocol?" | bhv | question_type: definition |
| "How to get key?" | pasje | question_type: method |
| "Work hours between 8-5?" | huisregels | extracted_times: 8:00, 17:00 |
| "Ziek morgen" | huisregels | extracted_dates: morgen |

---

## 🐛 Troubleshooting

### "Messages not being understood"
1. Check if Compromise.js loaded: `console.log(typeof compromise)`
2. Should return: `"function"`
3. If error, check internet (CDN access needed)

### "Semantic match not working"
1. Check message has correct keywords
2. Review `SEMANTIC_MAPPINGS` in Script.js
3. Add missing mappings if needed

### "Entity extraction not working"
1. Check message has extractable entities (dates, numbers)
2. Review regex patterns in `extractEntitiesFromMessage()`
3. Numbers must match pattern: `€50`, `50 euro`, etc.

---

## 🔧 Customization

### Add New Semantic Mapping
Edit **Script.js**, `SEMANTIC_MAPPINGS`:
```javascript
SEMANTIC_MAPPINGS = {
    "your_keyword": "your_intent",
}
```

### Add New Topic Keywords  
Edit **Chat.php**, `extractEntitiesFromMessage()`:
```php
$topicKeywords = [
    "your_intent" => ["keyword1", "keyword2"],
];
```

### Change Confidence Threshold
Edit **Chat.php**, `respond()`:
```php
if ($confidence >= 0.7) {  // Change from 0.8
    // Process semantic match
}
```

---

## 📚 More Info

- Full NLP documentation: See `NLP_GUIDE.md`
- Feature overview: See `FEATURES.md`
- Compromise.js docs: https://github.com/spencermountain/compromise

---

## ✨ Summary

Your bot now has:
- 🧠 **Real NLP** (Compromise.js)
- 🎯 **Semantic Understanding** (Maps user terminology to intents)
- 📊 **Entity Extraction** (Finds amounts, dates, times)
- 🔗 **Context Awareness** (Remembers conversation)
- 📈 **Better Matching** (Smart > Fuzzy > Keyword)

Try the test messages above and watch it understand! 🚀
