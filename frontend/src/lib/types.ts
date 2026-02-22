export type Paginated<T> = {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
};

export type Genre = {
  id: number;
  rawg_id: number;
  name: string;
  slug: string;
};

export type Platform = {
  id: number;
  rawg_id: number;
  name: string;
  slug: string;
};

export type GameDetail = {
  id: number;
  game_id: number;
  description_raw: string | null;
  esrb_rating: string | null;
  tba: boolean;
};

export type Review = {
  id: number;
  game_id: number;
  user_id: number;
  rating: number;
  title: string | null;
  body: string | null;
  created_at: string;
  updated_at: string;
  user?: { id: number; name: string };
};

export type Game = {
  id: number;
  rawg_id: number;
  name: string;
  slug: string;
  released_at: string | null;
  rating: number | null;
  background_image: string | null;
  description: string | null;
  metacritic: number | null;
  website: string | null;
  genres?: Genre[];
  platforms?: Platform[];
  detail?: GameDetail | null;
  reviews?: Review[];
};

export type AuthUser = {
  id: number;
  name: string;
  username: string;
  email: string;
};
