# TYPO3 extension messenger_demo


Dispatcher starten (erzeugt Messages):
```
vendor/bin/typo3 messenger-demo:dispatch
```

Worker starten (verarbeitet Messages):
```
vendor/bin/typo3 messenger:consume async -vv
```
