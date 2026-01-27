USE shopservice;

CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS financeiro_tags (
    financeiro_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (financeiro_id, tag_id),
    FOREIGN KEY (financeiro_id) REFERENCES financeiro(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);
