# Layout audit: reference (mindmarket.com) vs target (centaurian-run)

Tolerance ±1px on x/y/w/h. `rel.*` = measured relative to its section anchor (hero / timeline / callout). Delta = target − reference.


## 500x900  (27 mismatches: 25 high / 2 medium / 0 low)

### Timeline intro

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| timeline | rel.h | 6061.11 | 6026.78 | -34.33 | high |
| intro | rel.h | 739.08 | 704.75 | -34.33 | high |
| intro.text | rel.h | 240.3 | 205.97 | -34.33 | high |
| intro.button | rel.y | 326.73 | 292.41 | -34.32 | high |
| intro.button | rel.w | 181.41 | 215.89 | 34.48 | high |
| intro.ghost | rel.y | 389.44 | 355.11 | -34.33 | high |

### Cards

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| card1 | rel.h | 324.64 | 277.58 | -47.06 | high |
| card1.body | rel.h | 117.66 | 70.59 | -47.07 | high |
| card1.button | rel.y | 781.03 | 733.97 | -47.06 | high |
| card1.button | rel.w | 159.25 | 139.06 | -20.19 | high |
| card2 | rel.h | 371.7 | 277.58 | -94.12 | high |
| card2.body | rel.h | 164.72 | 70.59 | -94.13 | high |
| card2.button | rel.y | 1671.92 | 1577.8 | -94.12 | high |
| card2.button | rel.w | 194.55 | 171.7 | -22.85 | high |
| card3 | rel.h | 348.17 | 327.17 | -21 | high |
| card3.body | rel.h | 141.19 | 70.59 | -70.6 | high |
| card3.button | rel.y | 2737.2 | 2716.2 | -21 | high |
| card3.button | rel.w | 220.22 | 174.22 | -46 | high |
| card4 | rel.h | 348.17 | 277.58 | -70.59 | high |
| card4.body | rel.h | 141.19 | 70.59 | -70.6 | high |
| card4.button | rel.y | 4070.98 | 4000.39 | -70.59 | high |
| card4.button | rel.w | 190.94 | 182.13 | -8.81 | medium |

### Callout

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| callout | rel.h | 723.66 | 693.59 | -30.07 | high |
| callout.body | rel.h | 150.31 | 120.25 | -30.06 | high |
| callout.button | rel.y | 525.27 | 495.2 | -30.07 | high |
| callout.button | rel.w | 181.41 | 215.89 | 34.48 | high |
| callout.button | rel.x | 159.3 | 142.05 | -17.25 | medium |

### Page

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| document | breakpoints (ref only) | 640px 860px~1000px 860px~1000px~1400px |  | — | info |

## 700x900  (29 mismatches: 27 high / 2 medium / 0 low)

### Timeline intro

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| timeline | rel.h | 7694.42 | 7656.61 | -37.81 | high |
| intro | rel.h | 789.27 | 751.45 | -37.82 | high |
| intro.text | rel.h | 226.88 | 189.06 | -37.82 | high |
| intro.button | rel.y | 326.3 | 288.48 | -37.82 | high |
| intro.button | rel.w | 185 | 220.39 | 35.39 | high |
| intro.ghost | rel.y | 389.83 | 352.02 | -37.81 | high |

### Cards

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| card1 | rel.h | 357.53 | 309.56 | -47.97 | high |
| card1.body | rel.h | 143.91 | 95.94 | -47.97 | high |
| card1.button | rel.y | 1043.31 | 995.35 | -47.96 | high |
| card1.button | rel.w | 162.27 | 141.55 | -20.72 | high |
| card2 | rel.h | 405.5 | 285.58 | -119.92 | high |
| card2.body | rel.h | 191.88 | 71.95 | -119.93 | high |
| card2.button | rel.y | 2183.28 | 2063.36 | -119.92 | high |
| card2.button | rel.w | 198.48 | 175.05 | -23.43 | high |
| card3 | rel.h | 429.05 | 335.83 | -93.22 | high |
| card3.title | rel.h | 95.06 | 47.53 | -47.53 | high |
| card3.body | rel.y | 3457.56 | 3410.03 | -47.53 | high |
| card3.body | rel.h | 167.89 | 71.95 | -95.94 | high |
| card3.button | rel.y | 3651.09 | 3557.88 | -93.21 | high |
| card3.button | rel.w | 224.83 | 177.61 | -47.22 | high |
| card4 | rel.h | 381.52 | 285.58 | -95.94 | high |
| card4.body | rel.h | 167.89 | 71.95 | -95.94 | high |
| card4.button | rel.y | 5364.84 | 5268.91 | -95.93 | high |
| card4.button | rel.w | 194.78 | 185.73 | -9.05 | medium |

### Callout

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| callout | rel.h | 803.19 | 771.72 | -31.47 | high |
| callout.body | rel.h | 157.34 | 125.88 | -31.46 | high |
| callout.button | rel.y | 595.42 | 563.95 | -31.47 | high |
| callout.button | rel.w | 185 | 220.39 | 35.39 | high |
| callout.button | rel.x | 257.5 | 239.8 | -17.7 | medium |

### Page

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| document | breakpoints (ref only) | 640px 860px~1000px 860px~1000px~1400px |  | — | info |

## 999x900  (22 mismatches: 20 high / 2 medium / 0 low)

### Timeline intro

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| intro.button | rel.w | 190.38 | 227.13 | 36.75 | high |

### Cards

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| card1 | rel.h | 322.06 | 297.42 | -24.64 | high |
| card1.body | rel.h | 98.56 | 73.92 | -24.64 | high |
| card1.button | rel.y | 1337.14 | 1312.5 | -24.64 | high |
| card1.button | rel.w | 166.75 | 145.23 | -21.52 | high |
| card2 | rel.h | 346.7 | 272.78 | -73.92 | high |
| card2.body | rel.h | 123.2 | 49.28 | -73.92 | high |
| card2.button | rel.y | 2920.19 | 2846.26 | -73.93 | high |
| card2.button | rel.w | 204.38 | 180.03 | -24.35 | high |
| card3 | rel.h | 346.7 | 324.02 | -22.68 | high |
| card3.body | rel.h | 123.2 | 49.28 | -73.92 | high |
| card3.button | rel.y | 4981.31 | 4958.62 | -22.69 | high |
| card3.button | rel.w | 231.73 | 182.7 | -49.03 | high |
| card4 | rel.h | 346.7 | 272.78 | -73.92 | high |
| card4.body | rel.h | 123.2 | 49.28 | -73.92 | high |
| card4.button | rel.y | 7494.87 | 7420.95 | -73.92 | high |
| card4.button | rel.w | 200.53 | 191.13 | -9.4 | medium |

### Callout

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| callout | rel.h | 922.09 | 888.5 | -33.59 | high |
| callout.body | rel.h | 167.97 | 134.38 | -33.59 | high |
| callout.button | rel.y | 700.35 | 666.75 | -33.6 | high |
| callout.button | rel.w | 190.38 | 227.13 | 36.75 | high |
| callout.button | rel.x | 404.31 | 385.94 | -18.37 | medium |

### Page

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| document | breakpoints (ref only) | 640px 860px~1000px 860px~1000px~1400px |  | — | info |

## 1000x800  (34 mismatches: 30 high / 4 medium / 0 low)

### Header

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| nav.bar | w | 776.03 | 742.84 | -33.19 | high |
| nav.link[0] | x | 250.45 | 416.94 | 166.49 | high |
| nav.cta | x | 811.03 | 777.84 | -33.19 | high |
| nav.cta | w | 173.97 | 207.16 | 33.19 | high |
| nav.cta.label | x | 821.41 | 788.22 | -33.19 | high |
| nav.cta.label | w | 105.72 | 138.91 | 33.19 | high |
| nav.link[0] | w | 83.11 | 81.19 | -1.92 | medium |

### Timeline intro

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| timeline | rel.h | 3770.59 | 3739.94 | -30.65 | high |
| intro | rel.h | 627.52 | 596.86 | -30.66 | high |
| intro.text | rel.h | 214.59 | 183.94 | -30.65 | high |
| intro.button | rel.y | 329.59 | 298.94 | -30.65 | high |
| intro.button | rel.w | 171.47 | 204.66 | 33.19 | high |
| intro.ghost | rel.y | 387.84 | 357.19 | -30.65 | high |

### Cards

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| card1 | rel.h | 302.89 | 258.61 | -44.28 | high |
| card1.body | rel.h | 110.7 | 66.42 | -44.28 | high |
| card1.button | rel.y | 508.34 | 464.06 | -44.28 | high |
| card2 | rel.h | 347.17 | 258.61 | -88.56 | high |
| card2.body | rel.h | 154.98 | 66.42 | -88.56 | high |
| card2.button | rel.y | 1063.12 | 974.56 | -88.56 | high |
| card2.button | rel.w | 184.11 | 162.14 | -21.97 | high |
| card3 | rel.h | 325.03 | 304.55 | -20.48 | high |
| card3.body | rel.h | 132.84 | 66.42 | -66.42 | high |
| card3.button | rel.y | 1647.18 | 1626.7 | -20.48 | high |
| card3.button | rel.w | 208.81 | 164.55 | -44.26 | high |
| card4 | rel.h | 347.17 | 258.61 | -88.56 | high |
| card4.body | rel.h | 154.98 | 66.42 | -88.56 | high |
| card4.button | rel.y | 2498.87 | 2410.31 | -88.56 | high |
| card1.button | rel.w | 150.16 | 130.72 | -19.44 | medium |
| card4.button | rel.w | 180.64 | 172.16 | -8.48 | medium |

### Callout

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| callout | rel.h | 991.8 | 716.44 | -275.36 | high |
| callout.body | rel.h | 122.63 | 91.97 | -30.66 | high |
| callout.button | rel.y | 544.5 | 513.85 | -30.65 | high |
| callout.button | rel.w | 171.47 | 204.66 | 33.19 | high |
| callout.button | rel.x | 414.27 | 397.67 | -16.6 | medium |

### Page

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| document | breakpoints (ref only) | 640px 860px~1000px 860px~1000px~1400px |  | — | info |

## 1199x800  (34 mismatches: 30 high / 4 medium / 0 low)

### Header

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| nav.bar | w | 974.09 | 940.55 | -33.54 | high |
| nav.link[0] | x | 445.14 | 612.7 | 167.56 | high |
| nav.cta | x | 1009.09 | 975.55 | -33.54 | high |
| nav.cta | w | 174.91 | 208.45 | 33.54 | high |
| nav.cta.label | x | 1019.47 | 985.92 | -33.55 | high |
| nav.cta.label | w | 106.66 | 140.2 | 33.54 | high |
| nav.link[0] | w | 83.77 | 81.81 | -1.96 | medium |

### Timeline intro

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| timeline | rel.h | 4429.83 | 4398.33 | -31.5 | high |
| intro | rel.h | 661.33 | 629.83 | -31.5 | high |
| intro.text | rel.h | 189 | 157.5 | -31.5 | high |
| intro.button | rel.y | 316.44 | 284.94 | -31.5 | high |
| intro.button | rel.w | 172.41 | 205.95 | 33.54 | high |
| intro.ghost | rel.y | 374.69 | 343.19 | -31.5 | high |

### Cards

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| card1 | rel.h | 306.08 | 261.55 | -44.53 | high |
| card1.body | rel.h | 111.33 | 66.8 | -44.53 | high |
| card1.button | rel.y | 568.67 | 524.14 | -44.53 | high |
| card2 | rel.h | 328.34 | 239.28 | -89.06 | high |
| card2.body | rel.h | 133.59 | 44.53 | -89.06 | high |
| card2.button | rel.y | 1203.02 | 1113.95 | -89.07 | high |
| card2.button | rel.w | 185.19 | 162.97 | -22.22 | high |
| card3 | rel.h | 306.08 | 285.22 | -20.86 | high |
| card3.body | rel.h | 111.33 | 44.53 | -66.8 | high |
| card3.button | rel.y | 1907.58 | 1886.72 | -20.86 | high |
| card3.button | rel.w | 210.16 | 165.41 | -44.75 | high |
| card4 | rel.h | 328.34 | 239.28 | -89.06 | high |
| card4.body | rel.h | 133.59 | 44.53 | -89.06 | high |
| card4.button | rel.y | 2924.45 | 2835.39 | -89.06 | high |
| card1.button | rel.w | 150.86 | 131.22 | -19.64 | medium |
| card4.button | rel.w | 181.69 | 173.11 | -8.58 | medium |

### Callout

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| callout | rel.h | 1047.61 | 771.19 | -276.42 | high |
| callout.body | rel.h | 126 | 94.5 | -31.5 | high |
| callout.button | rel.y | 594.44 | 562.94 | -31.5 | high |
| callout.button | rel.w | 172.41 | 205.95 | 33.54 | high |
| callout.button | rel.x | 513.3 | 496.52 | -16.78 | medium |

### Page

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| document | breakpoints (ref only) | 640px 860px~1000px 860px~1000px~1400px |  | — | info |

## 1399x900  (34 mismatches: 31 high / 3 medium / 0 low)

### Header

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| nav.bar | w | 973.97 | 935.94 | -38.03 | high |
| nav.link[0] | x | 467.55 | 653.47 | 185.92 | high |
| nav.cta | x | 1100.16 | 1062.13 | -38.03 | high |
| nav.cta | w | 192.66 | 230.69 | 38.03 | high |
| nav.cta.label | x | 1111.78 | 1073.75 | -38.03 | high |
| nav.cta.label | w | 120.91 | 158.94 | 38.03 | high |
| nav.link[0] | w | 94.95 | 92.75 | -2.2 | medium |

### Timeline intro

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| timeline | rel.h | 5155.27 | 5119.58 | -35.69 | high |
| intro | rel.h | 758.16 | 722.47 | -35.69 | high |
| intro.text | rel.h | 214.13 | 178.44 | -35.69 | high |
| intro.button | rel.y | 358.56 | 322.88 | -35.68 | high |
| intro.button | rel.w | 195.16 | 233.19 | 38.03 | high |
| intro.ghost | rel.y | 424.31 | 388.63 | -35.68 | high |

### Cards

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| card1 | rel.h | 321.38 | 296.14 | -25.24 | high |
| card1.body | rel.h | 100.94 | 75.7 | -25.24 | high |
| card1.button | rel.y | 630.78 | 605.55 | -25.23 | high |
| card1.button | rel.w | 170.73 | 148.47 | -22.26 | high |
| card2 | rel.h | 371.84 | 270.91 | -100.93 | high |
| card2.body | rel.h | 151.41 | 50.47 | -100.94 | high |
| card2.button | rel.y | 1395.41 | 1294.47 | -100.94 | high |
| card2.button | rel.w | 209.64 | 184.47 | -25.17 | high |
| card3 | rel.h | 346.61 | 322.97 | -23.64 | high |
| card3.body | rel.h | 126.17 | 50.47 | -75.7 | high |
| card3.button | rel.y | 2218.25 | 2194.61 | -23.64 | high |
| card3.button | rel.w | 237.95 | 187.23 | -50.72 | high |
| card4 | rel.h | 346.61 | 270.91 | -75.7 | high |
| card4.body | rel.h | 126.17 | 50.47 | -75.7 | high |
| card4.button | rel.y | 3378.76 | 3303.06 | -75.7 | high |
| card4.button | rel.w | 205.67 | 195.95 | -9.72 | medium |

### Callout

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| callout | rel.h | 1194.39 | 881.13 | -313.26 | high |
| callout.body | rel.h | 142.75 | 107.06 | -35.69 | high |
| callout.button | rel.y | 681.06 | 645.38 | -35.68 | high |
| callout.button | rel.w | 195.16 | 233.19 | 38.03 | high |
| callout.button | rel.x | 601.92 | 582.91 | -19.01 | medium |

### Page

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| document | breakpoints (ref only) | 640px 860px~1000px 860px~1000px~1400px |  | — | info |

## 1600x900  (34 mismatches: 31 high / 3 medium / 0 low)

### Header

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| nav.bar | w | 952.34 | 914.31 | -38.03 | high |
| nav.link[0] | x | 493.48 | 700.66 | 207.18 | high |
| nav.cta | x | 1189.84 | 1151.81 | -38.03 | high |
| nav.cta | w | 192.66 | 230.69 | 38.03 | high |
| nav.cta.label | x | 1201.47 | 1163.44 | -38.03 | high |
| nav.cta.label | w | 120.91 | 158.94 | 38.03 | high |
| nav.link[0] | w | 103.45 | 101.25 | -2.2 | medium |

### Timeline intro

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| timeline | rel.h | 5811.39 | 5775.7 | -35.69 | high |
| intro | rel.h | 782.47 | 746.78 | -35.69 | high |
| intro.text | rel.h | 178.44 | 142.75 | -35.69 | high |
| intro.button | rel.y | 335.44 | 299.75 | -35.69 | high |
| intro.button | rel.w | 195.16 | 233.19 | 38.03 | high |
| intro.ghost | rel.y | 401.19 | 365.5 | -35.69 | high |

### Cards

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| card1 | rel.h | 412.39 | 361.92 | -50.47 | high |
| card1.body | rel.h | 126.17 | 75.7 | -50.47 | high |
| card1.button | rel.y | 855.05 | 804.58 | -50.47 | high |
| card1.button | rel.w | 170.73 | 148.47 | -22.26 | high |
| card2 | rel.h | 462.86 | 361.92 | -100.94 | high |
| card2.body | rel.h | 176.64 | 75.7 | -100.94 | high |
| card2.button | rel.y | 1620.2 | 1519.27 | -100.93 | high |
| card2.button | rel.w | 209.64 | 184.47 | -25.17 | high |
| card3 | rel.h | 523.95 | 475.08 | -48.87 | high |
| card3.body | rel.h | 176.64 | 75.7 | -100.94 | high |
| card3.button | rel.y | 2753.33 | 2704.45 | -48.88 | high |
| card3.button | rel.w | 237.95 | 187.23 | -50.72 | high |
| card4 | rel.h | 437.63 | 361.92 | -75.71 | high |
| card4.body | rel.h | 151.41 | 75.7 | -75.71 | high |
| card4.button | rel.y | 3892.19 | 3816.49 | -75.7 | high |
| card4.button | rel.w | 205.67 | 195.95 | -9.72 | medium |

### Callout

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| callout | rel.h | 1231.45 | 918.19 | -313.26 | high |
| callout.body | rel.h | 142.75 | 107.06 | -35.69 | high |
| callout.button | rel.y | 718.13 | 682.44 | -35.69 | high |
| callout.button | rel.w | 195.16 | 233.19 | 38.03 | high |
| callout.button | rel.x | 702.42 | 683.41 | -19.01 | medium |

### Page

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| document | breakpoints (ref only) | 640px 860px~1000px 860px~1000px~1400px |  | — | info |

## 1800x1000  (36 mismatches: 33 high / 3 medium / 0 low)

### Header

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| nav.bar | w | 1102.34 | 1064.31 | -38.03 | high |
| nav.link[0] | x | 668.48 | 875.66 | 207.18 | high |
| nav.cta | x | 1364.84 | 1326.81 | -38.03 | high |
| nav.cta | w | 192.66 | 230.69 | 38.03 | high |
| nav.cta.label | x | 1376.47 | 1338.44 | -38.03 | high |
| nav.cta.label | w | 120.91 | 158.94 | 38.03 | high |
| nav.link[0] | w | 103.45 | 101.25 | -2.2 | medium |

### Timeline intro

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| timeline | rel.h | 6499.72 | 6464.03 | -35.69 | high |
| intro | rel.h | 842.17 | 806.48 | -35.69 | high |
| intro.text | rel.h | 178.44 | 142.75 | -35.69 | high |
| intro.button | rel.y | 347.94 | 312.25 | -35.69 | high |
| intro.button | rel.w | 195.16 | 233.19 | 38.03 | high |
| intro.ghost | rel.y | 413.69 | 378 | -35.69 | high |

### Cards

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| card1 | rel.h | 387.16 | 361.92 | -25.24 | high |
| card1.body | rel.h | 100.94 | 75.7 | -25.24 | high |
| card1.button | rel.y | 900.02 | 874.79 | -25.23 | high |
| card1.button | rel.w | 170.73 | 148.47 | -22.26 | high |
| card2 | rel.h | 437.63 | 336.69 | -100.94 | high |
| card2.body | rel.h | 151.41 | 50.47 | -100.94 | high |
| card2.button | rel.y | 1783.22 | 1682.29 | -100.93 | high |
| card2.button | rel.w | 209.64 | 184.47 | -25.17 | high |
| card3 | rel.h | 498.72 | 413.98 | -84.74 | high |
| card3.title | rel.h | 122.19 | 61.09 | -61.1 | high |
| card3.body | rel.y | 2901.1 | 2840 | -61.1 | high |
| card3.body | rel.h | 151.41 | 75.7 | -75.71 | high |
| card3.button | rel.y | 3079.06 | 2994.33 | -84.73 | high |
| card3.button | rel.w | 237.95 | 187.23 | -50.72 | high |
| card4 | rel.h | 412.39 | 336.69 | -75.7 | high |
| card4.body | rel.h | 126.17 | 50.47 | -75.7 | high |
| card4.button | rel.y | 4313.64 | 4237.94 | -75.7 | high |
| card4.button | rel.w | 205.67 | 195.95 | -9.72 | medium |

### Callout

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| callout | rel.h | 1268.3 | 955.03 | -313.27 | high |
| callout.body | rel.h | 142.75 | 107.06 | -35.69 | high |
| callout.button | rel.y | 754.97 | 719.28 | -35.69 | high |
| callout.button | rel.w | 195.16 | 233.19 | 38.03 | high |
| callout.button | rel.x | 802.42 | 783.41 | -19.01 | medium |

### Page

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| document | breakpoints (ref only) | 640px 860px~1000px 860px~1000px~1400px |  | — | info |

## 2200x1200  (36 mismatches: 34 high / 2 medium / 0 low)

### Header

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| nav.bar | w | 1384.64 | 1342.16 | -42.48 | high |
| nav.link[0] | x | 924.84 | 1152.84 | 228 | high |
| nav.cta | x | 1697.14 | 1654.66 | -42.48 | high |
| nav.cta | w | 210.36 | 252.84 | 42.48 | high |
| nav.cta.label | x | 1710.02 | 1667.53 | -42.49 | high |
| nav.cta.label | w | 135.11 | 177.59 | 42.48 | high |
| nav.link[0] | w | 115.61 | 113.14 | -2.47 | medium |

### Timeline intro

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| timeline | rel.h | 7909.36 | 7869.47 | -39.89 | high |
| intro | rel.h | 994.59 | 954.7 | -39.89 | high |
| intro.text | rel.h | 199.45 | 159.56 | -39.89 | high |
| intro.button | rel.y | 398.45 | 358.56 | -39.89 | high |
| intro.button | rel.w | 217.86 | 260.34 | 42.48 | high |
| intro.ghost | rel.y | 471.7 | 431.81 | -39.89 | high |

### Cards

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| card1 | rel.h | 432.47 | 376.06 | -56.41 | high |
| card1.body | rel.h | 112.81 | 56.41 | -56.4 | high |
| card1.button | rel.y | 1071.96 | 1015.55 | -56.41 | high |
| card1.button | rel.w | 190.56 | 165.69 | -24.87 | high |
| card2 | rel.h | 460.67 | 376.06 | -84.61 | high |
| card2.body | rel.h | 141.02 | 56.41 | -84.61 | high |
| card2.button | rel.y | 2117.96 | 2033.35 | -84.61 | high |
| card2.button | rel.w | 234.05 | 205.91 | -28.14 | high |
| card3 | rel.h | 557.16 | 434.25 | -122.91 | high |
| card3.title | rel.h | 136.56 | 68.28 | -68.28 | high |
| card3.body | rel.y | 3524.68 | 3456.39 | -68.29 | high |
| card3.body | rel.h | 169.22 | 56.41 | -112.81 | high |
| card3.button | rel.y | 3723.58 | 3600.68 | -122.9 | high |
| card3.button | rel.w | 265.67 | 209 | -56.67 | high |
| card4 | rel.h | 460.67 | 376.06 | -84.61 | high |
| card4.body | rel.h | 141.02 | 56.41 | -84.61 | high |
| card4.button | rel.y | 5241.54 | 5156.93 | -84.61 | high |
| card4.button | rel.w | 229.61 | 218.73 | -10.88 | medium |

### Callout

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| callout | rel.h | 1452.02 | 1101.89 | -350.13 | high |
| callout.body | rel.h | 159.56 | 119.67 | -39.89 | high |
| callout.button | rel.x | 991.06 | 969.83 | -21.23 | high |
| callout.button | rel.y | 878.53 | 838.64 | -39.89 | high |
| callout.button | rel.w | 217.86 | 260.34 | 42.48 | high |

### Page

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| document | breakpoints (ref only) | 640px 860px~1000px 860px~1000px~1400px |  | — | info |

## 2560x1300  (34 mismatches: 32 high / 2 medium / 0 low)

### Header

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| nav.bar | w | 1632.45 | 1584.38 | -48.07 | high |
| nav.link[0] | x | 1122.52 | 1376.61 | 254.09 | high |
| nav.cta | x | 1989.95 | 1941.88 | -48.07 | high |
| nav.cta | w | 232.55 | 280.63 | 48.08 | high |
| nav.cta.label | x | 2004.39 | 1956.31 | -48.08 | high |
| nav.cta.label | w | 152.92 | 201 | 48.08 | high |
| nav.link[0] | w | 130.84 | 128.05 | -2.79 | medium |

### Timeline intro

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| timeline | rel.h | 9189.52 | 9144.38 | -45.14 | high |
| intro | rel.h | 1143.31 | 1098.17 | -45.14 | high |
| intro.text | rel.h | 225.7 | 180.56 | -45.14 | high |
| intro.button | rel.y | 452.83 | 407.69 | -45.14 | high |
| intro.button | rel.w | 246.3 | 294.38 | 48.08 | high |
| intro.ghost | rel.y | 535.45 | 490.31 | -45.14 | high |

### Cards

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| card1 | rel.h | 520.98 | 457.17 | -63.81 | high |
| card1.body | rel.h | 159.53 | 95.72 | -63.81 | high |
| card1.button | rel.y | 1269.62 | 1205.81 | -63.81 | high |
| card1.button | rel.w | 215.39 | 187.23 | -28.16 | high |
| card2 | rel.h | 552.89 | 425.27 | -127.62 | high |
| card2.body | rel.h | 191.44 | 63.81 | -127.63 | high |
| card2.button | rel.y | 2485.86 | 2358.23 | -127.63 | high |
| card2.button | rel.w | 264.61 | 232.77 | -31.84 | high |
| card3 | rel.h | 552.89 | 491.11 | -61.78 | high |
| card3.body | rel.h | 191.44 | 63.81 | -127.63 | high |
| card3.button | rel.y | 4241.94 | 4180.15 | -61.79 | high |
| card3.button | rel.w | 300.41 | 236.25 | -64.16 | high |
| card4 | rel.h | 520.98 | 425.27 | -95.71 | high |
| card4.body | rel.h | 159.53 | 63.81 | -95.72 | high |
| card4.button | rel.y | 6088.62 | 5992.9 | -95.72 | high |
| card4.button | rel.w | 259.58 | 247.28 | -12.3 | medium |

### Callout

| Element | Property | Reference value | Target value | Delta | Severity |
|---|---|---|---|---|---|
| callout | rel.h | 1655.77 | 1259.58 | -396.19 | high |
| callout.body | rel.h | 180.56 | 135.42 | -45.14 | high |
| callout.button | rel.x | 1156.84 | 1132.81 | -24.03 | high |
| callout.button | rel.y | 1007.09 | 961.96 | -45.13 | high |
| callout.button | rel.w | 246.3 | 294.38 | 48.08 | high |

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
| 500x900 | 27 | 25 | 2 | 0 |
| 700x900 | 29 | 27 | 2 | 0 |
| 999x900 | 22 | 20 | 2 | 0 |
| 1000x800 | 34 | 30 | 4 | 0 |
| 1199x800 | 34 | 30 | 4 | 0 |
| 1399x900 | 34 | 31 | 3 | 0 |
| 1600x900 | 34 | 31 | 3 | 0 |
| 1800x1000 | 36 | 33 | 3 | 0 |
| 2200x1200 | 36 | 34 | 2 | 0 |
| 2560x1300 | 34 | 32 | 2 | 0 |
