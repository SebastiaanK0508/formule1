import requests
from bs4 import BeautifulSoup
import re

def get_f1_news():
    url = "https://www.formula1.com/en/latest/headlines.html"
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    }
    
    try:
        response = requests.get(url, headers=headers)
        response.raise_for_status()
        
        soup = BeautifulSoup(response.text, 'html.parser')
        
        article_container = soup.find('div', class_=re.compile(r'LatestHeadlines-module_container__'))
        
        if not article_container:
            print("Fout: Kon de hoofdcontainer voor nieuwsartikelen niet vinden.")
            return None
        
        articles = article_container.find_all('a', href=re.compile(r'/en/latest/'))
        
        news_list = []
        for article in articles:
            title_element = article.find('p', class_=re.compile(r'ArticleListCard-module_title__'))
            
            if title_element:
                title = title_element.text.strip()
                link = "https://www.formula1.com" + article['href']
                
                # Controleer op dubbele artikelen, want soms worden ze meerdere keren geladen.
                if {'title': title, 'link': link} not in news_list:
                    news_list.append({
                        'title': title,
                        'link': link
                    })
            
        return news_list
        
    except requests.exceptions.RequestException as e:
        print(f"Kon de webpagina niet ophalen: {e}")
        return None

if __name__ == "__main__":
    f1_news = get_f1_news()
    if f1_news:
        print("Laatste nieuws van de officiële Formule 1-website:")
        for i, news in enumerate(f1_news[:5]):
            print(f"{i+1}. {news['title']}")
            print(f"   Link: {news['link']}\n")
    else:
        print("Geen nieuwsartikelen gevonden. Mogelijk is de website-structuur opnieuw veranderd.")