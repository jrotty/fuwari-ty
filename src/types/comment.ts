export interface CommentOwner {
  kind: string;
  displayName: string;
  avatar?: string;
  website?: string;
  emailHash?: string;
}

export interface CommentSubjectRef {
  group?: string;
  kind: string;
  name: string;
}

export interface CommentSpec {
  raw: string;
  content: string;
  owner: CommentOwner;
  subjectRef: CommentSubjectRef;
  top: boolean;
  allowNotification: boolean;
  approved: boolean;
  creationTime?: string;
}

export interface CommentStatus {
  phase?: string;
  replyCount: number;
  visibleReplyCount?: number;
  lastReplyTime?: string;
}

export interface Comment {
  apiVersion: string;
  kind: string;
  metadata: {
    name: string;
    creationTimestamp: string;
  };
  spec: CommentSpec;
  status: CommentStatus;
}

export interface Reply {
  apiVersion: string;
  kind: string;
  metadata: {
    name: string;
    creationTimestamp: string;
  };
  spec: {
    raw: string;
    content: string;
    owner: CommentOwner;
    commentName: string;
    quoteReply?: string;
    top: boolean;
    approved: boolean;
    creationTime?: string;
  };
}

export interface CommentListResponse {
  items: Comment[];
  total: number;
  page: number;
  size: number;
}

export interface ReplyListResponse {
  items: Reply[];
  total: number;
}
