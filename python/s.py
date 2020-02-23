import csv

with open('a.csv', 'rt') as f:
    reader = csv.reader(f, delimiter='\t')

    tako = [654, 53, 524, 54, 655, 94, 183, 55, 56, 589, 10, 346, 428, 640, 83, 237, 236, 682, 77, 156, 131, 76]
    real = [x - 2 for x in tako]

    print(real)

    target = {}

    for index, row in enumerate(reader):
        if index in real:
            name = row[4]
            shop = int(row[7])
            #            item = {'shop': shop, 'name': name}
            target[shop] = name

    print(target)
