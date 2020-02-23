import csv

with open('a.csv', 'rt') as f:
    reader = csv.reader(f, delimiter='\t')

    tako = [21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 42, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62]
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
