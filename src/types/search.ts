export interface SearchResult {
  hits: Hit[];
  keyword: string;
}

export interface Hit {
  title: string;
  permalink: string;
  description: string;
  score: number;
}
