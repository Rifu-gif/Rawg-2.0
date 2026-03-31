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
  bio?: string | null;
  image?: string | null;
  weekly_recommendation_emails?: boolean;
  followers_count?: number;
  following_count?: number;
};

export type SearchUser = {
  id: number;
  name: string;
  username: string;
};

export type PublicUserProfile = {
  id: number;
  name: string;
  username: string;
  bio?: string | null;
  image?: string | null;
  followers_count: number;
  following_count: number;
  is_following?: boolean;
  posts: UserPost[];
};

export type PostCategory = {
  id: number;
  name: string;
};

export type UserPost = {
  id: number;
  title: string;
  slug: string;
  content: string;
  image_url: string | null;
  published_at: string | null;
  created_at: string | null;
  updated_at: string | null;
  category: PostCategory | null;
  user: { id: number; name: string; username: string; image?: string | null } | null;
  is_favorited?: boolean;
  is_liked?: boolean;
  likes_count?: number;
  comments?: {
    id: number;
    content: string;
    created_at: string | null;
    user: { id: number; name: string; username: string } | null;
  }[];
};

export type RecommendedGame = {
  id: number;
  name: string;
  slug: string;
  background_image: string | null;
  rating: number | null;
  reviews_count: number;
  genres: Genre[];
  platforms: Platform[];
  recommendation_score: number;
  recommendation_reasons: string[];
  recommendation_explanation: string;
};

export type FeedRecommendations = {
  strategies_used: string[];
  insufficient_data: boolean;
  summary: string;
  games: {
    favorites_based_similarity: RecommendedGame[];
    review_based_similarity: RecommendedGame[];
    merged_similarity: RecommendedGame[];
  };
};

export type PostsFeedResponse = Paginated<UserPost> & {
  recommendations?: FeedRecommendations;
};
