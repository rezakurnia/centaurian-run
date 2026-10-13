# Layout audit: reference (mindmarket.com) vs target (centaurian-run)

Tolerance ±1px on x/y/w/h. `rel.*` = measured relative to its section anchor (hero / timeline / callout). Delta = target − reference.


## 375x812  (33 mismatches: 30 high / 3 medium / 0 low)

### Timeline intro

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| timeline | rel.h | 4683.66 | 4651.5 | -32.16 | high |
| intro | rel.h | 692.16 | 660 | -32.16 | high |
| intro.text | rel.h | 289.41 | 257.25 | -32.16 | high |
| intro.button | rel.y | 367.72 | 335.56 | -32.16 | high |
| intro.button | rel.w | 179.19 | 213.11 | 33.92 | high |
| intro.ghost | rel.y | 429.92 | 397.77 | -32.15 | high |

### Cards

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| card1 | rel.h | 365.53 | 295.83 | -69.7 | high |
| card1.body | rel.h | 162.64 | 92.94 | -69.7 | high |
| card1.button | rel.y | 686.53 | 616.83 | -69.7 | high |
| card2 | rel.h | 475.55 | 295.83 | -179.72 | high |
| card2.title | rel.h | 80.63 | 40.31 | -40.32 | high |
| card2.body | rel.y | 1171.97 | 1131.66 | -40.31 | high |
| card2.body | rel.h | 232.34 | 92.94 | -139.4 | high |
| card2.button | rel.y | 1429.4 | 1249.69 | -179.71 | high |
| card2.button | rel.w | 192.09 | 169.64 | -22.45 | high |
| card3 | rel.h | 452.31 | 345.02 | -107.29 | high |
| card3.title | rel.h | 80.63 | 40.31 | -40.32 | high |
| card3.body | rel.y | 1988.56 | 1948.25 | -40.31 | high |
| card3.body | rel.h | 209.11 | 92.94 | -116.17 | high |
| card3.button | rel.y | 2222.76 | 2115.47 | -107.29 | high |
| card3.button | rel.w | 217.36 | 172.11 | -45.25 | high |
| card4 | rel.h | 412 | 295.83 | -116.17 | high |
| card4.body | rel.h | 209.11 | 92.94 | -116.17 | high |
| card4.button | rel.y | 3182.79 | 3066.63 | -116.16 | high |
| card1.button | rel.w | 157.39 | 137.53 | -19.86 | medium |
| card4.button | rel.w | 188.56 | 179.89 | -8.67 | medium |

### Callout

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| callout | rel.h | 764.95 | 644.83 | -120.12 | high |
| callout.title | rel.h | 185.25 | 123.5 | -61.75 | high |
| callout.body | rel.y | 373.2 | 311.45 | -61.75 | high |
| callout.body | rel.h | 175.13 | 116.75 | -58.38 | high |
| callout.button | rel.y | 572.4 | 452.28 | -120.12 | high |
| callout.button | rel.w | 179.19 | 213.11 | 33.92 | high |
| callout.button | rel.x | 97.91 | 80.94 | -16.97 | medium |

### Page

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| document | breakpoints (ref only) | 640px 860px~1000px 860px~1000px~1400px |  | — | info |

## 768x1024  (27 mismatches: 25 high / 2 medium / 0 low)

### Timeline intro

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| timeline | rel.h | 8376.88 | 8337.88 | -39 | high |
| intro | rel.h | 800.89 | 761.89 | -39 | high |
| intro.text | rel.h | 195 | 156 | -39 | high |
| intro.button | rel.y | 298.84 | 259.84 | -39 | high |
| intro.button | rel.w | 186.23 | 221.94 | 35.71 | high |
| intro.ghost | rel.y | 362.66 | 323.66 | -39 | high |

### Cards

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| card1 | rel.h | 336.41 | 288.19 | -48.22 | high |
| card1.body | rel.h | 120.55 | 72.33 | -48.22 | high |
| card1.button | rel.y | 1097.09 | 1048.87 | -48.22 | high |
| card1.button | rel.w | 163.3 | 142.39 | -20.91 | high |
| card2 | rel.h | 384.63 | 288.19 | -96.44 | high |
| card2.body | rel.h | 168.77 | 72.33 | -96.44 | high |
| card2.button | rel.y | 2343.39 | 2246.95 | -96.44 | high |
| card2.button | rel.w | 199.83 | 176.19 | -23.64 | high |
| card3 | rel.h | 360.52 | 338.66 | -21.86 | high |
| card3.body | rel.h | 144.66 | 72.33 | -72.33 | high |
| card3.button | rel.y | 3903.84 | 3881.98 | -21.86 | high |
| card3.button | rel.w | 226.42 | 178.78 | -47.64 | high |
| card4 | rel.h | 384.63 | 288.19 | -96.44 | high |
| card4.body | rel.h | 168.77 | 72.33 | -96.44 | high |
| card4.button | rel.y | 5860.34 | 5763.9 | -96.44 | high |
| card4.button | rel.w | 196.11 | 186.97 | -9.14 | medium |

### Callout

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| callout | rel.h | 794.16 | 762.19 | -31.97 | high |
| callout.body | rel.h | 159.84 | 127.88 | -31.96 | high |
| callout.button | rel.y | 583.2 | 551.23 | -31.97 | high |
| callout.button | rel.w | 186.23 | 221.94 | 35.71 | high |
| callout.button | rel.x | 290.88 | 273.03 | -17.85 | medium |

### Page

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| document | breakpoints (ref only) | 640px 860px~1000px 860px~1000px~1400px |  | — | info |

## 1280x800  (34 mismatches: 31 high / 3 medium / 0 low)

### Header

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| nav.bar | w | 870.06 | 832.11 | -37.95 | high |
| nav.link[0] | x | 356.98 | 542.67 | 185.69 | high |
| nav.cta | x | 988.81 | 950.86 | -37.95 | high |
| nav.cta | w | 192.44 | 230.39 | 37.95 | high |
| nav.cta.label | x | 1000.44 | 962.48 | -37.96 | high |
| nav.cta.label | w | 120.69 | 158.64 | 37.95 | high |
| nav.link[0] | w | 94.8 | 92.61 | -2.19 | medium |

### Timeline intro

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| timeline | rel.h | 4744.61 | 4709.11 | -35.5 | high |
| intro | rel.h | 721.52 | 686.02 | -35.5 | high |
| intro.text | rel.h | 213 | 177.5 | -35.5 | high |
| intro.button | rel.y | 350 | 314.5 | -35.5 | high |
| intro.button | rel.w | 194.94 | 232.89 | 37.95 | high |
| intro.ghost | rel.y | 415.75 | 380.25 | -35.5 | high |

### Cards

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| card1 | rel.h | 345.88 | 295.47 | -50.41 | high |
| card1.body | rel.h | 126.02 | 75.61 | -50.41 | high |
| card1.button | rel.y | 621.11 | 570.7 | -50.41 | high |
| card1.button | rel.w | 170.58 | 148.36 | -22.22 | high |
| card2 | rel.h | 371.08 | 295.47 | -75.61 | high |
| card2.body | rel.h | 151.22 | 75.61 | -75.61 | high |
| card2.button | rel.y | 1299.73 | 1224.12 | -75.61 | high |
| card2.button | rel.w | 209.39 | 184.27 | -25.12 | high |
| card3 | rel.h | 371.08 | 322.33 | -48.75 | high |
| card3.body | rel.h | 151.22 | 50.41 | -100.81 | high |
| card3.button | rel.y | 2075.67 | 2026.92 | -48.75 | high |
| card3.button | rel.w | 237.64 | 187.03 | -50.61 | high |
| card4 | rel.h | 371.08 | 270.27 | -100.81 | high |
| card4.body | rel.h | 151.22 | 50.41 | -100.81 | high |
| card4.button | rel.y | 3137.48 | 3036.67 | -100.81 | high |
| card4.button | rel.w | 205.44 | 195.72 | -9.72 | medium |

### Callout

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| callout | rel.h | 1168.03 | 855.02 | -313.01 | high |
| callout.body | rel.h | 142 | 106.5 | -35.5 | high |
| callout.button | rel.y | 656.08 | 620.58 | -35.5 | high |
| callout.button | rel.w | 194.94 | 232.89 | 37.95 | high |
| callout.button | rel.x | 542.53 | 523.55 | -18.98 | medium |

### Page

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| document | breakpoints (ref only) | 640px 860px~1000px 860px~1000px~1400px |  | — | info |

## 1440x900  (34 mismatches: 31 high / 3 medium / 0 low)

### Header

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| nav.bar | w | 1009.84 | 971.81 | -38.03 | high |
| nav.link[0] | x | 450.73 | 657.91 | 207.18 | high |
| nav.cta | x | 1138.59 | 1100.56 | -38.03 | high |
| nav.cta | w | 192.66 | 230.69 | 38.03 | high |
| nav.cta.label | x | 1150.22 | 1112.19 | -38.03 | high |
| nav.cta.label | w | 120.91 | 158.94 | 38.03 | high |
| nav.link[0] | w | 103.45 | 101.25 | -2.2 | medium |

### Timeline intro

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| timeline | rel.h | 5296.42 | 5260.73 | -35.69 | high |
| intro | rel.h | 770.41 | 734.72 | -35.69 | high |
| intro.text | rel.h | 214.13 | 178.44 | -35.69 | high |
| intro.button | rel.y | 361.13 | 325.44 | -35.69 | high |
| intro.button | rel.w | 195.16 | 233.19 | 38.03 | high |
| intro.ghost | rel.y | 426.88 | 391.19 | -35.69 | high |

### Cards

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| card1 | rel.h | 424.97 | 374.5 | -50.47 | high |
| card1.body | rel.h | 151.41 | 100.94 | -50.47 | high |
| card1.button | rel.y | 811.46 | 761 | -50.46 | high |
| card1.button | rel.w | 170.73 | 148.47 | -22.26 | high |
| card2 | rel.h | 450.2 | 349.27 | -100.93 | high |
| card2.body | rel.h | 176.64 | 75.7 | -100.94 | high |
| card2.button | rel.y | 1479.92 | 1378.98 | -100.94 | high |
| card2.button | rel.w | 209.64 | 184.47 | -25.17 | high |
| card3 | rel.h | 450.2 | 401.33 | -48.87 | high |
| card3.body | rel.h | 176.64 | 75.7 | -100.94 | high |
| card3.button | rel.y | 2398.79 | 2349.92 | -48.87 | high |
| card3.button | rel.w | 237.95 | 187.23 | -50.72 | high |
| card4 | rel.h | 450.2 | 349.27 | -100.93 | high |
| card4.body | rel.h | 176.64 | 75.7 | -100.94 | high |
| card4.button | rel.y | 3547.4 | 3446.47 | -100.93 | high |
| card4.button | rel.w | 205.67 | 195.95 | -9.72 | medium |

### Callout

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| callout | rel.h | 1201.98 | 888.72 | -313.26 | high |
| callout.body | rel.h | 142.75 | 107.06 | -35.69 | high |
| callout.button | rel.y | 688.66 | 652.97 | -35.69 | high |
| callout.button | rel.w | 195.16 | 233.19 | 38.03 | high |
| callout.button | rel.x | 622.42 | 603.41 | -19.01 | medium |

### Page

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| document | breakpoints (ref only) | 640px 860px~1000px 860px~1000px~1400px |  | — | info |

## 1920x1080  (36 mismatches: 33 high / 3 medium / 0 low)

### Header

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| nav.bar | w | 1192.34 | 1154.31 | -38.03 | high |
| nav.link[0] | x | 773.48 | 980.66 | 207.18 | high |
| nav.cta | x | 1469.84 | 1431.81 | -38.03 | high |
| nav.cta | w | 192.66 | 230.69 | 38.03 | high |
| nav.cta.label | x | 1481.47 | 1443.44 | -38.03 | high |
| nav.cta.label | w | 120.91 | 158.94 | 38.03 | high |
| nav.link[0] | w | 103.45 | 101.25 | -2.2 | medium |

### Timeline intro

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| timeline | rel.h | 6912.66 | 6876.97 | -35.69 | high |
| intro | rel.h | 878 | 842.31 | -35.69 | high |
| intro.text | rel.h | 178.44 | 142.75 | -35.69 | high |
| intro.button | rel.y | 355.44 | 319.75 | -35.69 | high |
| intro.button | rel.w | 195.16 | 233.19 | 38.03 | high |
| intro.ghost | rel.y | 421.19 | 385.5 | -35.69 | high |

### Cards

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| card1 | rel.h | 387.16 | 361.92 | -25.24 | high |
| card1.body | rel.h | 100.94 | 75.7 | -25.24 | high |
| card1.button | rel.y | 942.11 | 916.87 | -25.24 | high |
| card1.button | rel.w | 170.73 | 148.47 | -22.26 | high |
| card2 | rel.h | 412.39 | 336.69 | -75.7 | high |
| card2.body | rel.h | 126.17 | 50.47 | -75.7 | high |
| card2.button | rel.y | 1855.6 | 1779.89 | -75.71 | high |
| card2.button | rel.w | 209.64 | 184.47 | -25.17 | high |
| card3 | rel.h | 498.72 | 413.98 | -84.74 | high |
| card3.title | rel.h | 122.19 | 61.09 | -61.1 | high |
| card3.body | rel.y | 3081.02 | 3019.92 | -61.1 | high |
| card3.body | rel.h | 151.41 | 75.7 | -75.71 | high |
| card3.button | rel.y | 3258.99 | 3174.25 | -84.74 | high |
| card3.button | rel.w | 237.95 | 187.23 | -50.72 | high |
| card4 | rel.h | 412.39 | 336.69 | -75.7 | high |
| card4.body | rel.h | 126.17 | 50.47 | -75.7 | high |
| card4.button | rel.y | 4581.6 | 4505.89 | -75.71 | high |
| card4.button | rel.w | 205.67 | 195.95 | -9.72 | medium |

### Callout

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| callout | rel.h | 1290.42 | 977.16 | -313.26 | high |
| callout.body | rel.h | 142.75 | 107.06 | -35.69 | high |
| callout.button | rel.y | 777.09 | 741.41 | -35.68 | high |
| callout.button | rel.w | 195.16 | 233.19 | 38.03 | high |
| callout.button | rel.x | 862.42 | 843.41 | -19.01 | medium |

### Page

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| document | breakpoints (ref only) | 640px 860px~1000px 860px~1000px~1400px |  | — | info |

## Asset byte-identity (sha1:size)

| Asset | Reference | Target | Same |
|---|---|---|---|
| /rive/basketball_player.riv | 5df724bb151d:27941 | 5df724bb151d:27941 | yes |
| /rive/binoculars.riv | 6aaec89b7218:24420 | 6aaec89b7218:24420 | yes |
| /rive/climber.riv | 9407f04d90d8:23797 | 9407f04d90d8:23797 | yes |
| /rive/composer.riv | 9e724585b85a:16966 | 9e724585b85a:16966 | yes |
| /rive/gamer.riv | 6063e043d774:23774 | 6063e043d774:23774 | yes |
| /rive/hero_animation.riv | 3de2f64ecea1:45423 | 3de2f64ecea1:45423 | yes |
| /rive/mindmarket_cursor_crop.riv | c536dabee4d2:3691 | c536dabee4d2:3691 | yes |
| /rive/pop_up_girl.riv | 34cbb8e63fbd:15230 | 34cbb8e63fbd:15230 | yes |
| /rive/scientist.riv | 50234a5b6594:23161 | 50234a5b6594:23161 | yes |
| /rive/shrug.riv | fe98fe8141da:21892 | fe98fe8141da:21892 | yes |
| /rive/soccer.riv | c99fd6900492:35284 | c99fd6900492:35284 | yes |
| /rive/trumpeter_1.riv | 2732a140c554:16881 | 2732a140c554:16881 | yes |
| /rive/trumpeter_2.riv | 6a3c127d567a:12229 | 6a3c127d567a:12229 | yes |
| /rive/unicycle.riv | 2e8abc0cd74e:28622 | 2e8abc0cd74e:28622 | yes |
| /timeline/clock-shape.svg | 6ec951818767:1345 | 6ec951818767:1345 | yes |
| /timeline/clock.svg | aa073f498953:2603 | aa073f498953:2603 | yes |
| /timeline/composer-shape-2.svg | ec8b1c26e7d9:500 | ec8b1c26e7d9:500 | yes |
| /timeline/composer-shape.svg | d3b0758c067e:1329 | d3b0758c067e:1329 | yes |
| /timeline/folder-shape.svg | fde231af2c0d:1308 | fde231af2c0d:1308 | yes |
| /timeline/folder.svg | 311cb94673d1:2772 | 311cb94673d1:2772 | yes |
| /timeline/gamer-shape.svg | a317b5d3151c:1027 | a317b5d3151c:1027 | yes |
| /timeline/hero-background-illustration.svg | e9db51d75815:4042 | e9db51d75815:4042 | yes |
| /timeline/joystick-shape.svg | 9c08fa039c0e:704 | 9c08fa039c0e:704 | yes |
| /timeline/joystick.svg | e2433029d2dc:6509 | e2433029d2dc:6509 | yes |
| /timeline/music-note-1.svg | b38b4543d7f1:4408 | b38b4543d7f1:4408 | yes |
| /timeline/music-note-2.svg | 23ff1250de13:2134 | 23ff1250de13:2134 | yes |
| /timeline/music-note-3.svg | 5868f0663249:2144 | 5868f0663249:2144 | yes |
| /timeline/plane-1.svg | 4324e5d41b06:2134 | 4324e5d41b06:2134 | yes |
| /timeline/plane-2.svg | 40baa3148004:1978 | 40baa3148004:1978 | yes |
| /timeline/plane-trace.svg | 79bbd15b8f1c:1815 | 79bbd15b8f1c:1815 | yes |
| /timeline/sheet.svg | 19afb4e30379:1638 | 19afb4e30379:1638 | yes |
| /timeline/soccer-circle-2.svg | b9cdc6ce5d56:225 | b9cdc6ce5d56:225 | yes |
| /timeline/soccer-circle.svg | c0de2cab0b1d:794 | c0de2cab0b1d:794 | yes |
| /timeline/soccer-shape-1.svg | 629a49308ed8:720 | 629a49308ed8:720 | yes |
| /timeline/soccer-shape-2.svg | f50521824306:807 | f50521824306:807 | yes |
| /timeline/soccer-shape-3.svg | 4be13b705291:820 | 4be13b705291:820 | yes |
| /timeline/soccer-shape-4.svg | 17620ff322a5:812 | 17620ff322a5:812 | yes |
| /timeline/soccer-trace.svg | bac49a03b28f:413 | bac49a03b28f:413 | yes |
| /timeline/timeline-end-green-back.svg | b4c21f378a8b:1038 | b4c21f378a8b:1038 | yes |
| /timeline/timeline-end-green-front.svg | 0730876a327c:16169 | 0730876a327c:16169 | yes |
| /timeline/timeline-end-green-middle.svg | 82d57f60efd1:2575 | 82d57f60efd1:2575 | yes |
| /timeline/timeline_end_cloud_1.svg | 5d5ad70e00b7:837 | 5d5ad70e00b7:837 | yes |
| /timeline/timeline_end_cloud_2.svg | 241bddbdcf08:811 | 241bddbdcf08:811 | yes |
| /timeline/timeline_end_cloud_3.svg | a502348d9e02:818 | a502348d9e02:818 | yes |
| /timeline/timeline_end_cloud_4.svg | fbbb2f768411:851 | fbbb2f768411:851 | yes |
| /timeline/timeline_end_cloud_5.svg | ae04b2c4ccaa:678 | ae04b2c4ccaa:678 | yes |
| /timeline/trumper-shape.svg | d564f953013c:803 | d564f953013c:803 | yes |


## Summary

| Viewport | Mismatches | High | Medium | Low |
|---|---|---|---|---|
| 375x812 | 33 | 30 | 3 | 0 |
| 768x1024 | 27 | 25 | 2 | 0 |
| 1280x800 | 34 | 31 | 3 | 0 |
| 1440x900 | 34 | 31 | 3 | 0 |
| 1920x1080 | 36 | 33 | 3 | 0 |
