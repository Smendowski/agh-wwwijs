import re
from cgi import parse_qs, escape
import json
import mysql.connector
from mysql.connector import errorcode


# Ustaw w ponizszych zmiennych dane dostepowe do bazy danych:
db_user = 's56'
db_name = 's56'
db_password = 'uh5rfbkn'


# Poniżej znajduje się glowna funkcja application, spelniajaca kryteria WSGI middleware.
# Zostanie ona wykonana przez serwer httpd kiedy nadejdzie nowe połączenie.
def application(environ, start_response):

    # Laczymy sie z baza danych. Jezeli wystapi blad to zwracamy komunikat,
    # a na poziomie HTTP, za pomoca naglowka Content-type, informujemy przegladarke,
    # ze typ zwracanych danych to HTML
    try:
        conn = mysql.connector.connect(user = db_user, database = db_name, password = db_password)
    except mysql.connector.Error as err:
        start_response('500 Internal Server Error', [('Content-type', 'text/html'), ('Access-Control-Allow-Origin', '*')])
        output = bytes('Nie mozna polaczyc sie z baza danych.', encoding = 'utf-8')
        yield output

    # Definiujemy zmienna, ktora bedzie zawierac zwracany do przegladarki ciag (zrodlo strony).
    output = ''

    # Pobieramy sciezke, na ktora wszedl uzytkownik (to co nastepuje po nazwie domeny
    # w pasku przegladarki).
    path = environ.get('PATH_INFO', '').lstrip('/').rstrip('/')

    # Do zmiennej query zapisujemy caly ciag parametrow podanych metoda GET.
    query = parse_qs(environ['QUERY_STRING'])

    # Teraz musimy zbadac na jaki adres wszedl uzytkownik i jaka funkcje mamy wykonac.
    # Sprawdzamy czy ktores wyrazenie regularne z tablicy urls zdefiniowanej na koncu
    # tego pliku (zajrzyj tam teraz i obejrzyj ta tablice), pasuje do odwiedzonego adresu
    # i jesli tak to wywolujemy odpowiadajaca mu funkcje, zapisujac wynik do zmiennej output.
    # Do funkcji przekazujemy polaczenie do bazy danych oraz parametry przekazane metoda GET.
    for regex, callback in urls:
        match = re.search(regex, path)
        if match is not None:
            output = callback(conn, query)

    # Jezeli zmienna output nie jest pusta, a wiec odwiedzony przez uzytkownika
    # adres jest prawidlowy i zostala wywolana jakas funkcja, ktora cos do niej
    # zapisala to zwracamy zawartosc tej zmiennej. Dodatkowo na poziomie
    # protokolu HTTP przekazujemy do przegladarki za pomoca naglowka Content-type,
    # ze typ zwracanych danych to ciag JSON.
    if output:
        start_response('200 OK', [('Content-type', 'application/json'), ('Access-Control-Allow-Origin', '*')])
        output = bytes(output, encoding = 'utf-8')
        yield output
    # Jezeli zmienna output jest pusta (else) to zwracamy komunikat bledu, a na poziomie
    # HTTP przekazujemy do przegladarki za pomoca naglowka Content-type, ze typ zwracanych
    # danych to HTML.
    else:
        start_response('404 NOT FOUND', [('Content-type', 'text/html'), ('Access-Control-Allow-Origin', '*')])
        output = bytes('Podany endpoint nie zostal odnaleziony.', encoding = 'utf-8')
        yield output

    conn.close()


# Ponizej znajduje sie funkcja index wywolywana po wejsciu na adres /
def index(conn, query):
    result = json.dumps({'api': '1.0.0'})
    return result


# Ponizej znajduje sie funkcja test wywolywana po wejsciu na adres /test
def test(conn, query):

    # Jezeli przekazano argument limit za pomoca metody GET to zostanie zapisany do zmiennej limit.
    limit = query.get('limit', [''])[0]

    # Jezeli zmienna limit nie jest pusta to sprowadzamy ja do formatu integer.
    if limit:
        limit = int(limit)
    # W przeciwnym wypadku ustawiamy domyslny limit (wartosc 5).
    else:
        limit = 5

    # Pobieramy dane z tabeli test. Do zapytania przekazujemy limit pobranych elementow.
    # Wiecej przykladow wykonywania zapytan do bazy danych w pythonie znajdziesz na stronie:
    # https://dev.mysql.com/doc/connector-python/en/connector-python-example-cursor-select.html
    cursor = conn.cursor(dictionary = True)

    cursor.execute("SELECT * FROM test ORDER BY id DESC LIMIT %s", (limit,))

    # Tworzymy tablice results, do ktorej zapiszemy dane z bazy danych.
    results = []

    # Zapisujemy do tablicy results odebrane wiersze z bazy danych.
    for obj in cursor:
        results.append(obj)

    cursor.close()

    # Konwertujemy tablice do formatu json.
    results = json.dumps(results)

    # Zwracamy dane - zostana one przypisane do zmiennej output w funkcji application.
    return results

def online(conn, query):

    cursor = conn.cursor(dictionary = True)
    cursor.execute("SELECT id, email FROM users WHERE last_seen IS NOT NULL AND (NOW() - last_seen) <= 300")
    results = []
    for obj in cursor:
        results.append(obj)

    cursor.close()
    results = json.dumps(results)
    return results

def articles(conn, query):
    limit = query.get('limit', [''])[0]
    if limit:
        limit = int(limit)
    else:
        limit = 10
    cursor = conn.cursor(dictionary = True)
    cursor.execute("SELECT id, title FROM articles ORDER BY id DESC LIMIT %s", (limit,))
    results = []
    for obj in cursor:
        results.append(obj)

    cursor.close()
    results = json.dumps(results)
    return results


# Ponizej definiujemy tablice zawierajaca dopuszczalne adresy w naszym API oraz funkcje,
# ktora maja byc wykonane po wejsciu na te adresy.
# ENDPOINTY
urls = [
    (r'^$', index),               # wejscie na /api uruchomi funkcje index
    (r'test$', test),             # wejscie na /api/test uruchomi funkcje test
    (r'online$', online),
    (r'articles$', articles),
]

