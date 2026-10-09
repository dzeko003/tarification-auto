# Calcul INDÉPENDANT du moteur PHP (rôle du « calcul Excel »), sur le barème fictif Sahel 2025.
# Régénérer : python3 reference_oracle_2025.py reference_profiles_2025.input.csv > reference_profiles_2025.csv
from decimal import Decimal as D, ROUND_HALF_UP
from datetime import date
import csv, sys

BASE = {
 'promenade_affaires': [(1,3,55000),(3,7,72000),(7,11,90000),(11,15,118000),(15,24,145000),(24,None,180000)],
 'transport_propre_compte': [(1,7,95000),(7,11,120000),(11,None,160000)],
 'transport_public_marchandises': [(1,11,150000),(11,None,210000)],
 'transport_public_voyageurs': [(1,7,165000),(7,11,195000),(11,None,240000)],
 'deux_roues': [(1,3,18000),(3,None,30000)],
}
USAGE = {'personnel': D('1.00'), 'professionnel': D('1.15')}
AGE = [(18,21,D('1.40')),(21,25,D('1.25')),(25,65,D('1.00')),(65,75,D('1.10')),(75,None,D('1.25'))]
LIC = [(0,1,D('1.30')),(1,3,D('1.15')),(3,None,D('1.00'))]
ZONE = {'douala':D('1.15'),'yaounde':D('1.10'),'autres_villes':D('1.00'),'zone_rurale':D('0.90')}

def band(bands, v):
    for lo, hi, c in bands:
        if v >= lo and (hi is None or v < hi): return c
    raise ValueError(v)

def years(a, b):
    y = b.year - a.year
    # anniversaire pas encore atteint ; un 29/02 compte le 01/03 les années non bissextiles
    if (b.month, b.day) < (a.month, a.day): y -= 1
    return y

def rnd(x): return int(x.quantize(D('1'), rounding=ROUND_HALF_UP))

rows = list(csv.DictReader(open(sys.argv[1])))
for r in rows:
    eff = date.fromisoformat(r['effective_date'])
    p = D(band(BASE[r['category']], int(r['fiscal_power'])))
    p *= USAGE[r['usage']]
    p *= band(AGE, years(date.fromisoformat(r['birth_date']), eff))
    p *= band(LIC, years(date.fromisoformat(r['license_date']), eff))
    p *= ZONE[r['zone']]
    p *= D(r['bonus_malus'])
    net = rnd(p)
    fees = 5000
    tca = rnd((net + fees) * D('0.145'))
    fga = rnd(net * D('0.025'))
    r['expected_net_premium'] = net
    r['expected_total'] = net + fees + tca + fga
w = csv.DictWriter(sys.stdout, fieldnames=rows[0].keys(), lineterminator='\n')
w.writeheader(); w.writerows(rows)
